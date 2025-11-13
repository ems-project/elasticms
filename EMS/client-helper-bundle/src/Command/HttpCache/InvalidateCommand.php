<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Command\HttpCache;

use EMS\ClientHelperBundle\Commands;
use EMS\CommonBundle\Common\Cache\Cache;
use EMS\CommonBundle\Common\Command\AbstractCommand;
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

    public function __construct(private readonly Cache $cacheManager)
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
        $dateTime = new \DateTimeImmutable();
        if ($this->purge || !$cache->isHit()) {
            $this->io->title('Purge all caches');
        } else {
            $lastHttpCacheInvalidate = $cache->get();
            if (!$lastHttpCacheInvalidate instanceof \DateTimeInterface) {
                throw new \LogicException('Last http cache invalidate date time is not valid.');
            }
            $this->io->title(\sprintf('Invalidate HTTP caches from %s', $lastHttpCacheInvalidate->format('Y-m-d H:i:s')));
        }
        $cache->set($dateTime);
        $this->cacheManager->save($cache);

        return self::SUCCESS;
    }
}
