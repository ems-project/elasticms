<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Unit\Common\StoreData\Service;

use EMS\CommonBundle\Common\Cache\Cache;
use EMS\CommonBundle\Common\StoreData\Service\StoreDataCacheService;
use EMS\CommonBundle\Common\StoreData\StoreDataHelper;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;

class StoreDataCacheServiceAiTest extends TestCase
{
    private Cache $cache;
    private StoreDataCacheService $service;

    #[\Override]
    protected function setUp(): void
    {
        $this->cache = $this->createMock(Cache::class);
        $this->service = new StoreDataCacheService($this->cache);
    }

    public function testSave(): void
    {
        $dataHelper = new StoreDataHelper('key', ['data' => 'value']);
        $cacheItem = $this->createMock(CacheItemInterface::class);

        $this->cache->expects($this->once())
            ->method('getItem')
            ->with('key')
            ->willReturn($cacheItem);

        $cacheItem->expects($this->once())
            ->method('set')
            ->with(['data' => 'value']);

        $this->cache->expects($this->once())
            ->method('save')
            ->with($cacheItem);

        $this->service->save($dataHelper);
    }

    public function testRead(): void
    {
        $cacheItem = $this->createMock(CacheItemInterface::class);

        $this->cache->expects($this->once())
            ->method('getItem')
            ->with('key')
            ->willReturn($cacheItem);

        $cacheItem->expects($this->once())
            ->method('get')
            ->willReturn(['data' => 'value']);

        $dataHelper = $this->service->read('key');
        $this->assertInstanceOf(StoreDataHelper::class, $dataHelper);
        $this->assertSame('key', $dataHelper->getKey());
        $this->assertSame(['data' => 'value'], $dataHelper->getData());
    }

    public function testDelete(): void
    {
        $this->cache->expects($this->once())
            ->method('delete')
            ->with('key');

        $this->service->delete('key');
    }
}
