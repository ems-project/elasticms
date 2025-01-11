<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Unit\Common\StoreData\Factory;

use EMS\CommonBundle\Common\Cache\Cache;
use EMS\CommonBundle\Common\StoreData\Factory\StoreDataCacheFactory;
use EMS\CommonBundle\Common\StoreData\Service\StoreDataCacheService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

class StoreDataCacheFactoryAiTest extends TestCase
{
    private Cache $cache;

    #[\Override]
    protected function setUp(): void
    {
        $this->cache = $this->createMock(Cache::class);
    }

    public function testGetType(): void
    {
        $factory = new StoreDataCacheFactory($this->cache);
        $this->assertSame(StoreDataCacheFactory::TYPE_CACHE, $factory->getType());
    }

    public function testCreateService(): void
    {
        $factory = new StoreDataCacheFactory($this->cache);
        $service = $factory->createService(['type' => StoreDataCacheFactory::TYPE_CACHE]);

        $this->assertInstanceOf(StoreDataCacheService::class, $service);
    }

    public function testCreateServiceWithInvalidType(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The option "type" with value "invalid" is invalid.');

        $factory = new StoreDataCacheFactory($this->cache);
        $factory->createService(['type' => 'invalid']);
    }

    public function testCreateServiceWithoutType(): void
    {
        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage('The required option "type" is missing.');

        $factory = new StoreDataCacheFactory($this->cache);
        $factory->createService([]);
    }
}
