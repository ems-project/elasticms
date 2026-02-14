<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Command\HttpCache;

use Elastica\Query\AbstractQuery;
use Elastica\Query\Range;
use EMS\ClientHelperBundle\Commands;
use EMS\ClientHelperBundle\Helper\Elasticsearch\ClientRequest;
use EMS\ClientHelperBundle\Helper\Elasticsearch\ClientRequestManager;
use EMS\CommonBundle\Common\Cache\Cache;
use EMS\CommonBundle\Common\Command\AbstractCommand;
use EMS\CommonBundle\Common\HttpCache\HttpCacheManager;
use EMS\CommonBundle\Helper\EmsFields;
use EMS\CommonBundle\Search\Search;
use EMS\Helpers\Standard\DateTime;
use EMS\Helpers\Standard\Type;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: Commands::HTTP_CACHE_INVALIDATE,
    description: 'Clear http cache in reverse proxies (e.g. varnish)',
    hidden: false
)]
class InvalidateCommand extends AbstractCommand
{
    private const string LAST_HTTP_CACHE_INVALIDATE_DATETIME = 'last-http-cache-invalidate-date-time';
    private const string OPTION_PURGE = 'purge';
    private const string OPTION_BULK_SIZE = 'bulk-size';
    private const string OPTION_SCROLL_TIMEOUT = 'scroll-timeout';
    private bool $purge;
    private readonly ClientRequest $client;
    private int $bulkSize;
    private string $scrollTimeout;

    public function __construct(
        private readonly Cache $cacheManager,
        private readonly ClientRequestManager $clientRequestManager,
        private readonly HttpCacheManager $httpCacheManager,
    ) {
        parent::__construct();
        $this->client = $this->clientRequestManager->getDefault();
    }

    #[\Override]
    protected function configure(): void
    {
        $this->addOption(self::OPTION_PURGE, 'p', InputOption::VALUE_NONE, 'Purge all caches.');
        $this->addOption(self::OPTION_BULK_SIZE, null, InputOption::VALUE_OPTIONAL, 'Bulk size.', 200);
        $this->addOption(self::OPTION_SCROLL_TIMEOUT, null, InputOption::VALUE_OPTIONAL, 'Scroll timeout.', '1m');
    }

    #[\Override]
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
        $this->purge = $this->getOptionBool(self::OPTION_PURGE);
        $this->bulkSize = $this->getOptionInt(self::OPTION_BULK_SIZE);
        $this->scrollTimeout = $this->getOptionString(self::OPTION_SCROLL_TIMEOUT);
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $cache = $this->cacheManager->getItem(self::LAST_HTTP_CACHE_INVALIDATE_DATETIME);
        if ($this->purge || !$cache->isHit()) {
            $lastPublishedDate = $this->purge();
        } else {
            $lastHttpCacheInvalidate = $cache->get();
            if (!$lastHttpCacheInvalidate instanceof \DateTimeImmutable) {
                throw new \LogicException('Last http cache invalidate date time is not valid.');
            }
            $lastPublishedDate = $this->ban($lastHttpCacheInvalidate);
        }
        $cache->set($lastPublishedDate);
        $this->cacheManager->save($cache);

        return self::SUCCESS;
    }

    private function purge(): \DateTimeImmutable
    {
        $this->io->title('Purge all caches');
        $search = $this->getSearch();
        $search->setSize(1);
        $search->setSort([EmsFields::CONTENT_PUBLISHED_DATETIME_FIELD => 'desc']);
        $search->setSize($this->bulkSize);

        $results = $this->client->commonSearch($search)->getResults();
        if (!isset($results[0])) {
            throw new \RuntimeException('No results found');
        }
        $lastPublishedDate = $results[0]->getSource()[EmsFields::CONTENT_PUBLISHED_DATETIME_FIELD] ?? null;
        $purged = $this->httpCacheManager->purgeAll();
        $this->io->note(\sprintf('%d HTTP cache(s) have been purged', $purged));

        return DateTime::create(Type::string($lastPublishedDate));
    }

    private function ban(\DateTimeImmutable $from): \DateTimeImmutable
    {
        $this->io->title(\sprintf('Invalidate HTTP caches from %s', $from->format('Y-m-d H:i:s')));
        $range = new Range(EmsFields::CONTENT_PUBLISHED_DATETIME_FIELD, [
            'gt' => $from->format('c'),
        ]);
        $search = $this->getSearch($range);
        $search->setSize(0);

        $total = $this->client->commonSearch($search)->getTotalHits();
        if (0 === $total) {
            $this->io->note('No hits found');

            return $from;
        }
        $search->setSize($this->bulkSize);
        $this->io->progressStart($total);
        $lastPublishedDate = $from;
        $tags = [];
        foreach ($this->client->commonScroll($search, $this->scrollTimeout) as $result) {
            $publishedDate = DateTime::create(Type::string($result->getSource()[EmsFields::CONTENT_PUBLISHED_DATETIME_FIELD] ?? null));
            if ($publishedDate > $lastPublishedDate) {
                $lastPublishedDate = $publishedDate;
            }
            $tags[] = $result->getId();
            if (0 === (\count($tags) % $this->bulkSize)) {
                $this->httpCacheManager->purgeByTags(...$tags);
            }
            $this->io->progressAdvance();
        }
        if ([] !== $tags) {
            $this->httpCacheManager->purgeByTags(...$tags);
        }
        $this->io->progressFinish();

        return $lastPublishedDate;
    }

    private function getSearch(?AbstractQuery $query = null): Search
    {
        $search = $this->client->initializeCommonSearch([], $query);
        $search->setSources([EmsFields::CONTENT_PUBLISHED_DATETIME_FIELD]);

        return $search;
    }
}
