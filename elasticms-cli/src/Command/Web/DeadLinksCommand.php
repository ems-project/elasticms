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
use Symfony\Component\Filesystem\Filesystem;
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
    private const string OPTION_CLEAR_CACHE = 'clear-cache';

    /** @var string[][] */
    private array $report = [];
    private string $cacheFolder;
    private CacheManager $cacheManager;
    private string $folder;
    private ?string $host = null;
    private bool $skipWarnings;
    private string $requestCacheFolder;

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
            ->addOption(self::OPTION_SKIP_WARNING, 's', InputOption::VALUE_NONE, 'Do not log warnings')
            ->addOption(self::OPTION_CLEAR_CACHE, null, InputOption::VALUE_NONE, 'Clear the existing caches')
            ->addOption(self::OPTION_CACHE_FOLDER, null, InputOption::VALUE_OPTIONAL, 'Path to a folder where cache will stored', \implode(DIRECTORY_SEPARATOR, [\getcwd(), 'var']));
    }

    #[\Override]
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
        $this->folder = $this->getArgumentString(self::ARG_FOLDER);
        $this->skipWarnings = $this->getOptionBool(self::OPTION_SKIP_WARNING);
        $this->cacheFolder = $this->getOptionString(self::OPTION_CACHE_FOLDER);
        $clearCache = $this->getOptionBool(self::OPTION_CLEAR_CACHE);
        $this->cacheManager = new CacheManager($this->cacheFolder, false);
        $filesystem = new Filesystem();
        $this->requestCacheFolder = \sprintf('%s/requests', $this->cacheFolder);
        $filesystem->mkdir($this->requestCacheFolder);
        if ($clearCache) {
            $this->cacheManager->clear();
            $filesystem = new Filesystem();
            $finder = new Finder();
            $finder->in($this->requestCacheFolder);
            foreach ($finder as $file) {
                $filesystem->remove($file->getRealPath());
            }
        }
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
            $url = new Url($link['url'], $referer, $link['text'] ?? null);
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
        $linkStatus = $this->getRequestStatus($url);

        if (!$linkStatus['isValid'] || !$linkStatus['hasResponse'] || !$linkStatus['statusCode'] || $linkStatus['message']) {
            $this->logError($url->getUrl(), $linkStatus['statusCode'] ?? 0, $linkStatus['message'] ?? 'Broken link', $referer, $link['text'] ?? '');

            return;
        }
        if (\in_array($linkStatus['statusCode'], [301, 302, 303, 307, 308], true)) {
            if ($linkStatus['location']) {
                $this->logWarning($url->getUrl(), $linkStatus['statusCode'], \sprintf('Redirection to %s', $linkStatus['location']), $referer, $link['text'] ?? '');
            } else {
                $this->logError($url->getUrl(), $linkStatus['statusCode'], 'Redirection without location', $referer, $link['text'] ?? '');
            }

            return;
        }
        if ($linkStatus['statusCode'] >= 300) {
            $this->logError($url->getUrl(), $linkStatus['statusCode'], 'Unexpected status code', $referer, $link['text'] ?? '');
        }
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

    /**
     * @return array{url: string, hasResponse: bool, message: ?string, isValid: bool, statusCode: ?int, location: ?string, timestamp: int}
     */
    private function getRequestStatus(Url $url): array
    {
        $hashName = \hash('sha256', $url->getUrl());
        $cacheFilename = \sprintf('%s/%s.json', $this->requestCacheFolder, $hashName);
        $now = new \DateTimeImmutable();
        if (\file_exists($cacheFilename)) {
            /** @var array{url: string, hasResponse: bool, message: ?string, isValid: bool, statusCode: ?int, location: ?string, timestamp: int} $cache */
            $cache = Json::decode(File::fromFilename($cacheFilename)->getContents());
            $age = $now->diff(\DateTimeImmutable::createFromTimestamp($cache['timestamp']))->m;
            if ($cache['url'] === $url->getUrl() && $age < 1) {
                return $cache;
            }
        }

        try {
            $result = $this->cacheManager->get($url->getUrl());
            $message = $result->getErrorMessage();
            $hasResponse = $result->hasResponse();
            $isValid = $result->isValid();
            if ($hasResponse) {
                $statusCode = $result->getResponse()->getStatusCode();
                $location = $result->getResponse()->hasHeader('Location') ? $result->getResponse()->getHeader('Location')[0] ?? null : null;
            }

            $data = [
                'url' => $url->getUrl(),
                'hasResponse' => $hasResponse,
                'message' => $message,
                'isValid' => $isValid,
                'statusCode' => $statusCode ?? null,
                'location' => $location ?? null,
                'timestamp' => $now->getTimestamp(),
            ];
        } catch (\Throwable $e) {
            $data = [
                'url' => $url->getUrl(),
                'hasResponse' => false,
                'message' => $e->getMessage(),
                'isValid' => false,
                'statusCode' => null,
                'location' => null,
                'timestamp' => $now->getTimestamp(),
            ];
        }
        File::putContents($cacheFilename, Json::encode($data));

        return $data;
    }
}
