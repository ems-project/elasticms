<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\Metric;

use EMS\CommonBundle\Common\Cache\Cache;
use EMS\Helpers\Standard\DateTime;
use Prometheus\CollectorRegistry;
use Prometheus\MetricFamilySamples;
use Prometheus\Storage\Adapter;
use Prometheus\Storage\APC;
use Prometheus\Storage\InMemory;
use Prometheus\Storage\Redis;
use Psr\Cache\CacheItemInterface;

class MetricCollector
{
    private ?CollectorRegistry $collectorRegistry = null;
    private ?Adapter $storageAdapter = null;

    private const string CACHE_VALIDITY = 'ems_metrics_validity';

    /**
     * @param iterable<MetricCollectorInterface> $collectors
     */
    public function __construct(private readonly Cache $cache, private readonly iterable $collectors)
    {
    }

    public function clear(): void
    {
        $this->getCollectorRegistry()->wipeStorage();
        $this->saveValidity([]);
    }

    public function isInMemoryStorage(): bool
    {
        return $this->getStorageAdapter() instanceof InMemory;
    }

    /**
     * @return MetricFamilySamples[]
     */
    public function getMetrics(): array
    {
        if ($this->isInMemoryStorage()) {
            $this->collect();
        }

        return $this->getCollectorRegistry()->getMetricFamilySamples();
    }

    public function collect(): void
    {
        $collectorRegistry = $this->getCollectorRegistry();

        foreach ($this->collectors as $collector) {
            $collector->collect($collectorRegistry);
        }
    }

    public function collectWithValidity(): void
    {
        $collectorRegistry = $this->getCollectorRegistry();

        $now = DateTime::create('now')->getTimestamp();
        $validity = $this->getValidity();

        foreach ($this->collectors as $collector) {
            $collectorValidity = $validity[$collector->getName()] ?? null;

            if (null !== $collectorValidity && $collectorValidity > $now) {
                continue;
            }

            $collector->collect($collectorRegistry);
            $validity[$collector->getName()] = $collector->validUntil();
        }

        $this->saveValidity($validity);
    }

    private function getCollectorRegistry(): CollectorRegistry
    {
        return $this->collectorRegistry ?: $this->createCollectorRegistry();
    }

    private function getStorageAdapter(): Adapter
    {
        return $this->storageAdapter ?: $this->createStorageAdapter();
    }

    private function createCollectorRegistry(): CollectorRegistry
    {
        $adapter = $this->getStorageAdapter();
        $this->collectorRegistry = new CollectorRegistry($adapter);

        return $this->collectorRegistry;
    }

    private function createStorageAdapter(): Adapter
    {
        $prefix = $this->cache->getPrefix();

        if ($this->cache->isApc()) {
            $storageAdapter = new APC($prefix);
        } elseif ($this->cache->isRedis()) {
            $storageAdapter = Redis::fromExistingConnection($this->cache->getRedis());
            $storageAdapter::setPrefix($prefix);
        } else {
            $storageAdapter = new InMemory();
        }

        $this->storageAdapter = $storageAdapter;

        return $storageAdapter;
    }

    /**
     * @return array<string, int>
     */
    private function getValidity(): array
    {
        $item = $this->getValidityCacheItem();
        $validity = $item->isHit() ? $item->get() : [];

        return \is_array($validity) ? $validity : [];
    }

    /**
     * @param array<string, int> $validity
     */
    private function saveValidity(array $validity): void
    {
        $item = $this->getValidityCacheItem();
        $item->set($validity);

        $this->cache->save($item);
    }

    private function getValidityCacheItem(): CacheItemInterface
    {
        return $this->cache->getItem(self::CACHE_VALIDITY);
    }
}
