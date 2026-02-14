<?php

declare(strict_types=1);

namespace App\CLI\Command\Web;

use App\CLI\Client\HttpClient\CacheManager;
use App\CLI\Commands;
use EMS\CommonBundle\Common\Admin\AdminHelper;
use EMS\CommonBundle\Common\Command\AbstractCommand;
use EMS\CommonBundle\Common\Spreadsheet\SpreadsheetGeneratorService;
use EMS\CommonBundle\Contracts\Spreadsheet\SpreadsheetGeneratorServiceInterface;
use EMS\CommonBundle\Exception\NotParsableUrlException;
use EMS\CommonBundle\Helper\Url;
use EMS\Helpers\File\File;
use EMS\Helpers\File\TempFile;
use EMS\Helpers\Standard\Json;
use EMS\Helpers\Standard\Type;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\HeaderUtils;

#[AsCommand(
    name: Commands::DEAD_LINKS_REPORT,
    description: 'Audit the external links of a web site (from a eMS a11y audit)',
    hidden: false
)]
class DeadLinksCommand extends AbstractCommand
{
    private const string ARG_FOLDER = 'folder';
    private const string OPTION_SKIP_WARNING = 'skip-warning';
    private const string OPTION_CACHE_FOLDER = 'cache-folder';

    /** @var string[][] */
    private array $report = [];
    private string $cacheFolder;
    private CacheManager $cacheManager;
    private string $folder;
    private ?string $host = null;
    private bool $skipWarnings;

    public function __construct(
        private readonly AdminHelper $adminHelper,
        private readonly SpreadsheetGeneratorServiceInterface $spreadsheetGeneratorService,
    ) {
        parent::__construct();
    }

    #[\Override]
    protected function configure(): void
    {
        $this
            ->addArgument(self::ARG_FOLDER, InputArgument::REQUIRED, 'Folder path containing the JSON files for an eMS accessibility (A11Y) audit')
            ->addOption(self::OPTION_SKIP_WARNING, 's', InputOption::VALUE_OPTIONAL, 'Do not log warnings')
            ->addOption(self::OPTION_CACHE_FOLDER, null, InputOption::VALUE_OPTIONAL, 'Path to a folder where cache will stored', \implode(DIRECTORY_SEPARATOR, [\getcwd(), 'var']));
    }

    #[\Override]
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
        $this->folder = $this->getArgumentString(self::ARG_FOLDER);
        $this->skipWarnings = $this->getOptionBool(self::OPTION_SKIP_WARNING);
        $this->cacheFolder = $this->getOptionString(self::OPTION_CACHE_FOLDER);
        $this->cacheManager = new CacheManager($this->cacheFolder, false);
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $finder = new Finder();
        $finder->files()
            ->in($this->folder)
            ->name('*.json');
        $this->io->progressStart($finder->count());
        foreach ($finder as $file) {
            $this->auditPage(Json::decode($file->getContents()));
            $this->io->progressAdvance();
        }
        $this->io->progressFinish();

        $tempFile = TempFile::create();
        $filename = \sprintf('dead-links-report-%s.xlsx', \date('YmdHis'));
        $this->spreadsheetGeneratorService->generateSpreadsheetFile([
            SpreadsheetGeneratorService::CONTENT_DISPOSITION => HeaderUtils::DISPOSITION_ATTACHMENT,
            SpreadsheetGeneratorService::CONTENT_FILENAME => $filename,
            SpreadsheetGeneratorService::WRITER => SpreadsheetGeneratorService::XLSX_WRITER,
            SpreadsheetGeneratorService::SHEETS => [[
                'rows' => [['Level', 'Status', 'Message', 'URL', 'Referer', 'Text'], ...$this->report],
                'name' => 'dead-links',
            ]],
        ], $tempFile->path);

        $coreApi = $this->adminHelper->getCoreApi();
        if ($coreApi->isAuthenticated()) {
            $hash = $coreApi->file()->uploadFile($tempFile->path, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $filename);
            $this->io->success(\sprintf('%s/data/file/%s?type=%s&name=%s', $coreApi->getBaseUrl(), $hash, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', \urlencode($filename)));
        } else {
            File::putContents($filename, $tempFile->getContents());
            $this->io->success(\sprintf('The file %s has been saved in the current working directory', $filename));
        }

        return self::SUCCESS;
    }

    /**
     * @param mixed[] $page
     */
    private function auditPage(array $page): void
    {
        if (null === $this->host) {
            $this->host = Type::string($page['host']);
        }
        $referer = Type::string($page['url']);
        $status = (int) ($page['status_code'] ?? 0);
        if ($status < 200 || $status > 299) {
            $this->logError($referer, $status, 'Wrong return code', 'n/a', 'n/a');
        }
        foreach ($page['links'] ?? [] as $link) {
            $this->auditLink($referer, $link);
        }
    }

    /**
     * @param mixed[] $link
     */
    private function auditLink(string $referer, array $link): void
    {
        try {
            $url = new Url($link['url'], $referer, $link['text']);
        } catch (NotParsableUrlException) {
            $this->logError($link['url'], 0, 'not parsable url', $referer, $link['text'] ?? '');

            return;
        }
        if (!$url->isCrawlable()) {
            $this->logWarning($link['url'], 0, 'not crawlable url', $referer, $link['text'] ?? '');

            return;
        }
        if ($this->host === $url->getHost()) {
            return;
        }
        if (\in_array($link['type'] ?? null, ['link', 'script'])) {
            return;
        }
        //        $result = $this->cacheManager->get($url->getUrl());
    }

    private function logError(string $url, int $status, string $error, string $referer, string $text): void
    {
        $this->log('Error', $url, $status, $error, $referer, $text);
    }

    private function logWarning(string $url, int $status, string $error, string $referer, string $text): void
    {
        if ($this->skipWarnings) {
            return;
        }
        $this->log('Warning', $url, $status, $error, $referer, $text);
    }

    private function log(string $level, string $url, int $status, string $error, string $referer, string $text): void
    {
        $this->report[] = [
            $level,
            (string) $status,
            $error,
            $url,
            $referer,
            $text,
        ];
    }
}
