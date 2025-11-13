<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Command\HttpCache;

use EMS\ClientHelperBundle\Commands;
use EMS\ClientHelperBundle\Helper\Elasticsearch\ClientRequestManager;
use EMS\CommonBundle\Common\Cache\Cache;
use EMS\CommonBundle\Common\Command\AbstractCommand;
use EMS\CommonBundle\Helper\EmsFields;
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
    private const LAST_HTTP_CACHE_INVALIDATE_DATETIME = 'last-http-cache-invalidate-date-time';
    private const string OPTION_PURGE = 'purge';
    private bool $purge;

    public function __construct(private readonly Cache $cacheManager, private readonly ClientRequestManager $manager)
    {
        parent::__construct();
    }

    #[\Override]
    protected function configure(): void
    {
        $this->addOption(self::OPTION_PURGE, 'p', InputOption::VALUE_NONE, 'Purge all caches.');
    }

    #[\Override]
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
        $this->purge = $this->getOptionBool(self::OPTION_PURGE);
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

        $client = $this->manager->getDefault();
        $search = $client->initializeCommonSearch([]);
        $search->setSort([EmsFields::CONTENT_PUBLISHED_DATETIME_FIELD => 'desc']);
        $search->setSize(1);
        $search->setSources([EmsFields::CONTENT_PUBLISHED_DATETIME_FIELD]);
        $results = $client->commonSearch($search)->getResults();
        if (!isset($results[0])) {
            throw new \RuntimeException('No results found');
        }
        $lastPublishedDate = $results[0]->getSource()[EmsFields::CONTENT_PUBLISHED_DATETIME_FIELD] ?? null;

        return DateTime::create(Type::string($lastPublishedDate));
    }

    private function ban(\DateTimeImmutable $from): \DateTimeImmutable
    {
        $this->io->title(\sprintf('Invalidate HTTP caches from %s', $from->format('Y-m-d H:i:s')));

        return $from;
    }
}
