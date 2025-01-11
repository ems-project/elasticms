<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\StoreData\Service;

use EMS\CommonBundle\Common\Cache\Cache;
use EMS\CommonBundle\Common\StoreData\StoreDataHelper;

class StoreDataCacheService implements StoreDataServiceInterface
{
    public function __construct(private readonly Cache $cache, private readonly ?int $ttl = null)
    {
    }

    #[\Override]
    public function save(StoreDataHelper $data): void
    {
        $cacheItem = $this->cache->getItem($data->getKey());
        $cacheItem->set($data->getData());
        if (null !== $this->ttl) {
            $cacheItem->expiresAfter($this->ttl);
        }
        $this->cache->save($cacheItem);
    }

    #[\Override]
    public function read(string $key): ?StoreDataHelper
    {
        $cacheItem = $this->cache->getItem($key);

        return new StoreDataHelper($key, $cacheItem->get() ?? []);
    }

    #[\Override]
    public function delete(string $key): void
    {
        $this->cache->delete($key);
    }

    #[\Override]
    public function gc(): void
    {
    }
}
