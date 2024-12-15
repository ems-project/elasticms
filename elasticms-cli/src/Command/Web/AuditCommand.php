<?php

declare(strict_types=1);

namespace App\CLI\Command\Web;

use App\CLI\Client\Audit\AuditManager;
use App\CLI\Client\Audit\AuditResult;
use App\CLI\Client\Audit\Cache;
use App\CLI\Client\Audit\Report;
use App\CLI\Client\HttpClient\CacheManager;
use App\CLI\Client\HttpClient\UrlReport;
use App\CLI\Commands;
use Elastica\Query\BoolQuery;
use Elastica\Query\Exists;
use Elastica\Query\Range;
use Elastica\Query\Terms;
use EMS\CommonBundle\Common\Admin\AdminHelper;
use EMS\CommonBundle\Common\Command\AbstractCommand;
use EMS\CommonBundle\Contracts\CoreApi\Endpoint\Data\DataInterface;
use EMS\CommonBundle\Elasticsearch\Document\EMSSource;
use EMS\CommonBundle\Exception\NotParsableUrlException;
use EMS\CommonBundle\Helper\Url;
use EMS\Helpers\Standard\Json;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: Commands::WEB_AUDIT,
    description: 'Audit (security headers, content, locale, accessibility) website.',
    hidden: false
)]
class AuditCommand extends AbstractCommand
{
    private const ARG_URL = 'url';
    private const OPTION_CONTINUE = 'continue';
    private const OPTION_CACHE_FOLDER = 'cache-folder';
    private const OPTION_SAVE_FOLDER = 'save-folder';
    private const OPTION_MAX_UPDATES = 'max-updates';
    private const OPTION_IGNORE_REGEX = 'ignore-regex';
    private const OPTION_TIKA_BASE_URL = 'tika-base-url';
    private const OPTION_TIKA_MAX_SIZE = 'tika-max-size';
    private const OPTION_DRY_RUN = 'dry-run';
    private const OPTION_PA11Y = 'pa11y';
    private const OPTION_TIKA = 'tika';
    private const OPTION_ALL = 'all';
    private const OPTION_LIGHTHOUSE = 'lighthouse';
    private const OPTION_CONTENT_TYPE = 'content-type';
    private const OPTION_BASE_URL = 'base-url';
    private ConsoleLogger $logger;
    private string $jsonPath;
    private string $cacheFolder;
    private bool $continue;
    private bool $dryRun;
    private int $maxUpdate;
    private Url $startingUrl;
    private Cache $auditCache;
    private string $contentType;
    private CacheManager $cacheManager;
    private bool $lighthouse;
    private bool $pa11y;
    private bool $tika;
    private bool $all;
    private ?string $ignoreRegex = null;
    private ?string $tikaBaseUrl = null;
    private float $tikaMaxSize;
    private ?string $saveFolder = null;
    /** @var string[] */
    private array $audited = [];
    private string $baseUrl;

    public function __construct(private readonly AdminHelper $adminHelper)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                self::ARG_URL,
                InputArgument::REQUIRED,
                'Website landing page\'s URL'
            )
            ->addOption(
                self::OPTION_CONTINUE,
                null,
                InputOption::VALUE_NONE,
                'Continue import from last know updated document'
            )
            ->addOption(self::OPTION_DRY_RUN, null, InputOption::VALUE_NONE, 'don\'t update elasticms')
            ->addOption(self::OPTION_PA11Y, null, InputOption::VALUE_NONE, 'Add a pa11y accessibility audit')
            ->addOption(self::OPTION_LIGHTHOUSE, null, InputOption::VALUE_NONE, 'Add a Lighthouse audit')
            ->addOption(self::OPTION_TIKA, null, InputOption::VALUE_NONE, 'Add a Tika audit')
            ->addOption(self::OPTION_ALL, null, InputOption::VALUE_NONE, 'Add all audits (Tika, pa11y, lighthouse')
            ->addOption(self::OPTION_CONTENT_TYPE, null, InputOption::VALUE_OPTIONAL, 'Audit\'s content type', 'audit')
            ->addOption(self::OPTION_CACHE_FOLDER, null, InputOption::VALUE_OPTIONAL, 'Path to a folder where cache will stored', \implode(DIRECTORY_SEPARATOR, [\getcwd(), 'var']))
            ->addOption(self::OPTION_SAVE_FOLDER, null, InputOption::VALUE_OPTIONAL, 'If defined, the audit document will be also saved as JSON in the specified folder')
            ->addOption(self::OPTION_MAX_UPDATES, null, InputOption::VALUE_OPTIONAL, 'Maximum number of document that can be updated in 1 batch (if the continue option is activated)', 500)
            ->addOption(self::OPTION_IGNORE_REGEX, null, InputOption::VALUE_OPTIONAL, 'Regex that will defined paths \'(^\/path_pattern|^\/second_pattern\' to ignore')
            ->addOption(self::OPTION_BASE_URL, null, InputOption::VALUE_OPTIONAL, 'Only scans urls starting with this base url', '/')
            ->addOption(self::OPTION_TIKA_BASE_URL, null, InputOption::VALUE_OPTIONAL, 'Tika\'s server base url. If not defined a JVM will be instantiated')
            ->addOption(self::OPTION_TIKA_MAX_SIZE, null, InputOption::VALUE_OPTIONAL, 'File bigger than this limit are not send to Tika [in MB]', 5);
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
        $this->logger = new ConsoleLogger($output);
        $this->startingUrl = new Url($this->getArgumentString(self::ARG_URL));
        $this->cacheFolder = $this->getOptionString(self::OPTION_CACHE_FOLDER);
        $this->jsonPath = \sprintf('%s/%s.json', $this->cacheFolder, $this->startingUrl->getHost());
        $this->continue = $this->getOptionBool(self::OPTION_CONTINUE);
        $this->dryRun = $this->getOptionBool(self::OPTION_DRY_RUN);
        $this->lighthouse = $this->getOptionBool(self::OPTION_LIGHTHOUSE);
        $this->pa11y = $this->getOptionBool(self::OPTION_PA11Y);
        $this->tika = $this->getOptionBool(self::OPTION_TIKA);
        $this->all = $this->getOptionBool(self::OPTION_ALL);
        $this->contentType = $this->getOptionString(self::OPTION_CONTENT_TYPE);
        $this->maxUpdate = $this->getOptionInt(self::OPTION_MAX_UPDATES);
        $this->ignoreRegex = $this->getOptionStringNull(self::OPTION_IGNORE_REGEX);
        $this->saveFolder = $this->getOptionStringNull(self::OPTION_SAVE_FOLDER);
        $this->tikaBaseUrl = $this->getOptionStringNull(self::OPTION_TIKA_BASE_URL);
        $this->tikaMaxSize = $this->getOptionFloat(self::OPTION_TIKA_MAX_SIZE);
        $this->baseUrl = $this->getOptionString(self::OPTION_BASE_URL);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->adminHelper->getCoreApi()->isAuthenticated()) {
            $this->io->error(\sprintf('Not authenticated for %s, run ems:admin:login', $this->adminHelper->getCoreApi()->getBaseUrl()));

            return self::EXECUTE_ERROR;
        }

        $this->io->section('Load config');
        $this->cacheManager = new CacheManager($this->cacheFolder, false);
        $api = $this->adminHelper->getCoreApi()->data($this->contentType);

        $this->auditCache = $this->loadAuditCache();
        if ($this->continue) {
            $this->auditCache->resume();
        } else {
            $this->auditCache->reset();
            $this->cacheManager->clear();
        }
        $report = $this->auditCache->getReport();

        $auditManager = new AuditManager($this->logger, $this->all, $this->pa11y, $this->lighthouse, $this->tika, $this->tikaBaseUrl, \intval($this->tikaMaxSize * 1024 * 1024), $this->baseUrl);
        $this->io->title(\sprintf('Starting auditing %s', $this->startingUrl->getUrl()));
        $counter = 0;
        $finish = true;
        while ($this->auditCache->hasNext()) {
            $url = $this->auditCache->next();
            if (!\str_starts_with($url->getPath(), $this->baseUrl)) {
                $this->logger->notice('Ignored as not in the base URL');
                $report->addIgnoredUrl($url, 'Ignored as not in the base URL');
                continue;
            }
            if (null !== $this->ignoreRegex && \preg_match(\sprintf('/%s/', $this->ignoreRegex), $url->getPath())) {
                $this->logger->notice('Ignored by regex');
                $report->addIgnoredUrl($url, 'Ignored by regex');
                continue;
            }
            $result = $this->cacheManager->get($url->getUrl());
            if (!$result->hasResponse()) {
                $this->logger->notice('Broken link');
                $report->addBrokenLink(new UrlReport($url, 0, $result->getErrorMessage()));
                continue;
            }
            if (\in_array($result->getResponse()->getStatusCode(), [301, 302, 303, 307, 308])) {
                $this->logger->notice('Redirect');
                if (!$result->getResponse()->hasHeader('Location')) {
                    $report->addBrokenLink(new UrlReport($url, $result->getResponse()->getStatusCode(), 'Redirect without Location header'));
                    continue;
                }
                $location = $result->getResponse()->getHeader('Location')[0] ?? null;
                if (null === $location) {
                    throw new \RuntimeException('Unexpected missing Location');
                }
                try {
                    $link = new Url($location, $url->getUrl());
                    if ($this->auditCache->inHosts($link->getHost())) {
                        $this->auditCache->addUrl($link);
                        $report->addWarning($url, [\sprintf('Redirect (%d) to %s', $result->getResponse()->getStatusCode(), $location)]);
                    } else {
                        $report->addWarning($url, [\sprintf('External redirect (%d) to %s', $result->getResponse()->getStatusCode(), $location)]);
                    }
                } catch (NotParsableUrlException $e) {
                    $report->addWarning($url, [\sprintf('Redirect to %s', $e->getMessage())]);
                }
                continue;
            }
            if ($result->getResponse()->getStatusCode() >= 300) {
                $report->addWarning($url, [\sprintf('Return code %d', $result->getResponse()->getStatusCode())]);
            }
            $urlHash = $this->auditCache->getUrlHash($url);
            $auditResult = $auditManager->analyze($url, $result, $report, \in_array($urlHash, $this->audited, true));
            $this->logger->notice('Analyzed');
            $this->treatLinks($auditResult, $report);
            if (\in_array($urlHash, $this->audited)) {
                continue;
            }

            if (!$auditResult->isValid()) {
                $report->addBrokenLink($auditResult->getUrlReport());
                $this->logger->notice('Broken links added');
            }
            if (\count($auditResult->getPa11y()) > 0) {
                $report->addAccessibilityError($url->getUrl(), \count($auditResult->getPa11y()), $auditResult->getAccessibility());
                $this->logger->notice('Accessibility report added');
            }
            if (\count($auditResult->getSecurityWarnings()) > 0) {
                $report->addSecurityError($url->getUrl(), \count($auditResult->getSecurityWarnings()), $auditResult->getBestPractices());
                $this->logger->notice('Security warnings added');
            }
            if (\count($auditResult->getWarnings()) > 0) {
                $report->addWarning($url, $auditResult->getWarnings());
                $this->logger->notice('Warnings added');
            }
            $this->logger->notice('Ready');
            if (!$this->dryRun) {
                $assets = $auditResult->uploadAssets($this->adminHelper->getCoreApi()->file());
                $rawData = $auditResult->getRawData($assets);
                if ($this->pa11y && !isset($rawData['pa11y'])) {
                    $rawData['pa11y'] = [];
                }
                if (!isset($rawData['security'])) {
                    $rawData['security'] = [];
                }
                if (!isset($rawData['links'])) {
                    $rawData['links'] = [];
                }
                $this->logger->debug(Json::encode($rawData, true));
                $retry = 0;
                while (!$this->saveAudit($api, $urlHash, $rawData, $auditResult->getUrl()->getUrl(null, false, false))) {
                    if ($retry++ < 10) {
                        continue;
                    }
                    $this->logger->error(\sprintf('Has try to upload the audit result for %s 10 times', $auditResult->getUrl()->getUrl(null, false, false)));
                }
            } else {
                $this->logger->debug(Json::encode($auditResult->getRawData([]), true));
            }

            $this->audited[] = $urlHash;
            if (null != $this->saveFolder) {
                \file_put_contents(\sprintf('%s/%s.json', $this->saveFolder, $this->auditCache->getUrlHash($auditResult->getUrl())), Json::encode($auditResult->getRawData([]), true));
            }
            $this->auditCache->setReport($report);
            $this->auditCache->save($this->jsonPath);
            $this->logger->notice('Cache saved');
            if (++$counter >= $this->maxUpdate && $this->continue) {
                $finish = false;
                break;
            }
            $this->logger->notice('Progress');
            $this->auditCache->progress($output);
        }
        $this->auditCache->progressFinish($output, $counter);

        if (!$this->auditCache->hasNext() && !$this->dryRun) {
            try {
                $this->deleteNonUpdated();
            } catch (\Throwable $e) {
                $this->io->warning(\sprintf('The command was not able to delete old documents: %s', $e->getMessage()));
            }
        }

        $this->io->section('Save cache and report');
        $this->auditCache->save($this->jsonPath, $finish);
        $filename = \sprintf('Audit-%s-%s.xlsx', $this->startingUrl->getHost(), \date('Ymd-His'));
        $mimetype = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
        $filepath = $report->generateXslxReport();
        $reportLogsHash = $this->adminHelper->getCoreApi()->file()->uploadFile($filepath, $mimetype, $filename);
        $this->io->writeln(\sprintf('Audit logs: %s', $this->adminHelper->getCoreApi()->file()->downloadLink($reportLogsHash)));

        return self::EXECUTE_SUCCESS;
    }

    protected function loadAuditCache(): Cache
    {
        if (!\file_exists($this->jsonPath)) {
            return new Cache($this->startingUrl);
        }
        $contents = \file_get_contents($this->jsonPath);
        if (false === $contents) {
            throw new \RuntimeException('Unexpected false config file');
        }
        $cache = Cache::deserialize($contents);
        $cache->addUrl($this->startingUrl);

        return $cache;
    }

    private function treatLinks(AuditResult $auditResult, Report $report): void
    {
        foreach ($auditResult->getLinks() as $link) {
            if (!$link->isCrawlable()) {
                $report->addIgnoredUrl($link, 'Non-crawlable url');
                continue;
            }

            if ($this->auditCache->inHosts($link->getHost())) {
                $this->auditCache->addUrl($link);
                $auditResult->addInternalLink($link);
            } else {
                $this->logger->notice(\sprintf('Test external link %s', $link->getUrl()));
                try {
                    $urlReport = $this->cacheManager->testUrl($link);
                    if (!$urlReport->isValid()) {
                        $report->addBrokenLink($urlReport);
                    }
                    $auditResult->addExternalLink($urlReport);
                } catch (\Throwable $e) {
                    $report->addBrokenLink(new UrlReport($link, 0, $e->getMessage()));
                }
            }
        }
    }

    private function deleteNonUpdated(): void
    {
        $alias = $this->adminHelper->getCoreApi()->meta()->getDefaultContentTypeEnvironmentAlias($this->contentType);
        $boolQuery = new BoolQuery();
        $boolQuery->addMust(new Terms('host', [$this->startingUrl->getHost()]));
        $boolQuery->addMust(new Terms(EMSSource::FIELD_CONTENT_TYPE, [$this->contentType]));
        $boolQuery->addMust(new Range('timestamp', [
            'lt' => $this->auditCache->getStartedDate(),
        ]));
        if ('/' === $this->baseUrl) {
            $boolQuery->setMinimumShouldMatch(1);
            $boolQuery->addShould(new Terms('base_url', [$this->baseUrl]));
            $boolMustNotBase = new BoolQuery();
            $boolMustNotBase->addMustNot(new Exists('base_url'));
            $boolQuery->addShould($boolMustNotBase);
        } else {
            $boolQuery->addMust(new Terms('base_url', [$this->baseUrl]));
        }

        $boolQuery->setMinimumShouldMatch(1);
        $boolQuery->addShould(new Terms('base_url', [$this->baseUrl]));
        $boolMustNotBase = new BoolQuery();
        $boolMustNotBase->addMustNot(new Exists('base_url'));
        $boolQuery->addShould(new Terms('base_url', [$this->baseUrl]));

        $body = Json::encode([
            'index' => [$alias],
            'body' => ['query' => $boolQuery->toArray()],
            'size' => 50,
        ]);

        $command = \sprintf('emsco:revision:delete  --mode=by-query --query=\'%s\'', $body);
        $this->adminHelper->getCoreApi()->admin()->runCommand($command, $this->output);
    }

    /**
     * @param array<string, mixed> $rawData
     */
    private function saveAudit(DataInterface $api, string $urlHash, array $rawData, string $url): bool
    {
        try {
            $api->save($urlHash, $rawData);
            $this->logger->notice('Document saved');

            return true;
        } catch (\Throwable $e) {
            $this->logger->error(\sprintf('Error while saving the report for %s, go sleep for 60s: %s', $url, $e->getMessage()));
            \sleep(60);

            return false;
        }
    }
}
