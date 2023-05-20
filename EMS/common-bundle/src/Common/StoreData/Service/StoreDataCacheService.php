<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\StoreData\Service;

use EMS\CommonBundle\Common\Cache\Cache;
use EMS\CommonBundle\Common\StoreData\StoreDataHelper;

class StoreDataCacheService implements StoreDataServiceInterface
{
    public function __construct(private readonly Cache $cache)
    {
    }

    public function save(StoreDataHelper $data): void
    {
        $cacheItem = $this->cache->getItem($data->getKey());
        $cacheItem->set($data->getData());
        $this->cache->save($cacheItem);
    }

    public function read(string $key): ?StoreDataHelper
    {
        $cacheItem = $this->cache->getItem($key);

        return new StoreDataHelper($key, $cacheItem->get() ?? []);
    }

    public function delete(string $key): void
    {
        $this->cache->delete($key);
    }
}
