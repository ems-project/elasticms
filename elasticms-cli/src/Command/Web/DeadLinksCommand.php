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
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Contracts\Translation\TranslatorInterface;

use function Symfony\Component\Translation\t;

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
    private const string OPTION_IGNORE_SSL = 'ignore-ssl';
    private const string OPTION_LOCALE = 'locale';

    /** @var string[][] */
    private array $report = [];
    private string $cacheFolder;
    private CacheManager $cacheManager;
    private string $folder;
    private ?string $host = null;
    private bool $skipWarnings;
    private string $requestCacheFolder;
    private string $locale;

    public function __construct(
        private readonly AdminHelper $adminHelper,
        private readonly SpreadsheetGeneratorServiceInterface $spreadsheetGeneratorService,
        private readonly TranslatorInterface $translator,
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
            ->addOption(self::OPTION_IGNORE_SSL, null, InputOption::VALUE_NONE, 'Ignore SSL certificates')
            ->addOption(self::OPTION_LOCALE, null, InputOption::VALUE_OPTIONAL, 'Language of the report', 'en')
            ->addOption(self::OPTION_CACHE_FOLDER, null, InputOption::VALUE_OPTIONAL, 'Path to a folder where cache will stored', \implode(DIRECTORY_SEPARATOR, [\getcwd(), 'var']));
    }

    #[\Override]
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
        $this->folder = $this->getArgumentString(self::ARG_FOLDER);
        $this->skipWarnings = $this->getOptionBool(self::OPTION_SKIP_WARNING);
        $this->cacheFolder = $this->getOptionString(self::OPTION_CACHE_FOLDER);
        $this->locale = $this->getOptionString(self::OPTION_LOCALE);
        $clearCache = $this->getOptionBool(self::OPTION_CLEAR_CACHE);
        $verify = !$this->getOptionBool(self::OPTION_IGNORE_SSL);
        $this->cacheManager = new CacheManager($this->cacheFolder, false, $verify);
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
                'rows' => [['Level', 'Status', 'Message', 'Scheme', 'Problem', 'URL', 'Location', 'Referer', 'Text', 'Error'], ...$this->report],
                'name' => 'dead-links',
            ]],
        ], $tempFile->path);

        $coreApi = $this->adminHelper->getCoreApi();
        if ($coreApi->isAuthenticated()) {
            $hash = $coreApi->file()->uploadFile($tempFile->path, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $filename);
            $this->io->success(\sprintf('%sdata/file/%s?type=%s&name=%s', $coreApi->getBaseUrl(), $hash, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', \urlencode($filename)));
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
        try {
            $scheme = new Url($referer)->getScheme();
        } catch (\Throwable) {
            $position = \strpos($referer, '://');
            $scheme = false === $position || $position <= 0 || $position > 10 ? '' : \substr($referer, 0, $position);
        }
        $status = (int) ($page['status_code'] ?? 0);
        if ($status < 200 || $status > 299) {
            $this->logError($referer, $scheme, $status, 'Broken link', 'n/a', 'n/a');
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
            $url = $link['url'];
            $position = \strpos((string) $url, '://');
            $scheme = false === $position || $position <= 0 || $position >= 10 ? '' : \substr((string) $url, 0, $position);
            $this->logError($url, $scheme, 0, 'Not parsable url', $referer, $link['text'] ?? '');

            return;
        }
        if (!$url->isCrawlable()) {
            $this->logWarning($link['url'], $url->getScheme(), 0, 'Not crawlable url', $referer, $link['text'] ?? '');

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
            $this->logError($url->getUrl(), $url->getScheme(), $linkStatus['statusCode'] ?? 0, 'Broken link', $referer, $link['text'] ?? '', $linkStatus['location'] ?? null, $linkStatus['message'] ?? null);

            return;
        }
        if (\in_array($linkStatus['statusCode'], [301, 302, 303, 307, 308], true)) {
            if ($linkStatus['location']) {
                $this->logWarning($url->getUrl(), $url->getScheme(), $linkStatus['statusCode'], 'Redirection to location', $referer, $link['text'] ?? '', $linkStatus['location'], $linkStatus['message'] ?? null);
            } else {
                $this->logError($url->getUrl(), $url->getScheme(), $linkStatus['statusCode'], 'Redirection without location', $referer, $link['text'] ?? '', null, $linkStatus['message'] ?? null);
            }

            return;
        }
        if ($linkStatus['statusCode'] >= 300) {
            $this->logError($url->getUrl(), $url->getScheme(), $linkStatus['statusCode'], 'Unexpected status code', $referer, $link['text'] ?? '', $linkStatus['location'] ?? null, $linkStatus['message'] ?? null);
        }
    }

    private function logError(string $url, string $scheme, int $status, string $message, string $referer, string $text, ?string $location = null, ?string $error = null): void
    {
        $this->log('Error', $url, $scheme, $status, $message, $referer, $text, $location, $error);
    }

    private function logWarning(string $url, string $scheme, int $status, string $message, string $referer, string $text, ?string $location = null, ?string $error = null): void
    {
        if ($this->skipWarnings) {
            return;
        }
        $this->log('Warning', $url, $scheme, $status, $message, $referer, $text, $location, $error);
    }

    private function log(string $level, string $url, string $scheme, int $status, string $message, string $referer, string $text, ?string $location, ?string $error): void
    {
        $problemDescription = $this->getProblemDescription($scheme, $status, $location);
        $problemDescription = $this->translator->trans($problemDescription->getMessage(), $problemDescription->getParameters(), $problemDescription->getDomain(), $this->locale);

        $this->report[] = [
            $level,
            (string) $status,
            $message,
            $scheme,
            $problemDescription,
            $url,
            $location ?? '',
            $referer,
            $text,
            $error ?? '',
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
        } catch (\Throwable $throwable) {
            $data = [
                'url' => $url->getUrl(),
                'hasResponse' => false,
                'message' => $throwable->getMessage(),
                'isValid' => false,
                'statusCode' => null,
                'location' => null,
                'timestamp' => $now->getTimestamp(),
            ];
        }
        File::putContents($cacheFilename, Json::encode($data));

        return $data;
    }

    private function getProblemDescription(string $scheme, int $status, ?string $location): TranslatableMessage
    {
        if ('ems' === $scheme) {
            return t('web.audit.missing-document');
        }
        if (0 === $status && \in_array($scheme, ['http', 'https'], true)) {
            return t('web.audit.server-gone');
        }
        if (0 === $status && 'file' === $scheme) {
            return t('web.audit.local-file');
        }
        if ($status >= 300 && $status < 400 && null !== $location) {
            if ($this->isBlockedByEnterprisePolicyUrl($location)) {
                return t('web.audit.blocked-by-enterprise-policy');
            }
            if ($this->looksBroken($location)) {
                return t('web.audit.page-not-found');
            }
            if (\str_starts_with($location, 'https://') && 'http' === $scheme) {
                return t('web.audit.permanent-redirect');
            }
        }

        return match ($status) {
            301, 303, 308 => t('web.audit.permanent-redirect'),
            302, 307 => t('web.audit.temporary-redirect'),
            403 => t('web.audit.access-denied'),
            404, 410 => t('web.audit.page-not-found'),
            500 => t('web.audit.internal-server-error'),
            502, 503, 504 => t('web.audit.server-gone'),
            default => t('web.audit.problem-without-solution'),
        };
    }

    private function isBlockedByEnterprisePolicyUrl(string $url): bool
    {
        $host = \parse_url($url, \PHP_URL_HOST);
        if (!\is_string($host)) {
            return false;
        }

        return \in_array(\strtolower($host), [
            'block.sse.cisco.com',
            'block.opendns.com',
            'malware.opendns.com',
            'phish.opendns.com',
            'www1.dlinksearch.com',
            'bpb.opendns.com',
            'url.fortinet.net',
        ], true);
    }

    private function looksBroken(string $location): bool
    {
        $path = \parse_url($location, \PHP_URL_PATH);
        if (!\is_string($path)) {
            return false;
        }

        $slug = \basename(\rtrim($path, '/'));

        return 1 === \preg_match('/(?<!\d)(404|500)(?!\d)/', $slug);
    }
}
