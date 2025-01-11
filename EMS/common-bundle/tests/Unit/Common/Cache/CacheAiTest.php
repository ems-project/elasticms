<?php

declare(strict_types=1);

namespace EMS\Tests\CommonBundle\Unit\Common\Cache;

use EMS\CommonBundle\Common\Cache\Cache;
use EMS\Helpers\File\TempDirectory;
use PHPUnit\Framework\TestCase;

class CacheAiTest extends TestCase
{
    private string $cacheDir;
    private Cache $cache;

    #[\Override]
    protected function setUp(): void
    {
        $this->cacheDir = TempDirectory::create()->path;
    }

    protected function removeDirectory($path): void
    {
        $files = \glob($path.'/*');
        foreach ($files as $file) {
            \is_dir($file) ? $this->removeDirectory($file) : \unlink($file);
        }
        \rmdir($path);
    }

    #[\Override]
    protected function tearDown(): void
    {
        \array_map('unlink', \glob("$this->cacheDir/*.*"));
        $this->removeDirectory($this->cacheDir);
    }

    public function testIsApc(): void
    {
        $this->cache = new Cache(['type' => Cache::TYPE_APC, 'prefix' => 'test_prefix_'], $this->cacheDir);
        $this->assertTrue($this->cache->isApc());
    }

    public function testIsRedis(): void
    {
        $this->cache = new Cache(['type' => Cache::TYPE_REDIS, 'prefix' => 'test_prefix_', 'redis' => ['host' => '127.0.0.1', 'port' => 6379]], $this->cacheDir);
        $this->assertTrue($this->cache->isRedis());
    }

    public function testIsFilesystem(): void
    {
        $this->cache = new Cache(['type' => Cache::TYPE_FILE_SYSTEM, 'prefix' => 'test_prefix_'], $this->cacheDir);
        $this->assertTrue($this->cache->isFilesystem());
    }

    public function testGetType(): void
    {
        $this->cache = new Cache(['type' => Cache::TYPE_APC, 'prefix' => 'test_prefix_'], $this->cacheDir);
        $this->assertEquals(Cache::TYPE_APC, $this->cache->getType());
    }

    public function testGetItem(): void
    {
        $this->cache = new Cache(['type' => Cache::TYPE_FILE_SYSTEM, 'prefix' => 'test_prefix_'], $this->cacheDir);
        $item = $this->cache->getItem('test_key');
        $this->assertFalse($item->isHit());
    }

    public function testSave(): void
    {
        $this->cache = new Cache(['type' => Cache::TYPE_FILE_SYSTEM, 'prefix' => 'test_prefix_'], $this->cacheDir);
        $item = $this->cache->getItem('test_key');
        $item->set('test_value');
        $this->cache->save($item);

        $savedItem = $this->cache->getItem('test_key');
        $this->assertTrue($savedItem->isHit());
        $this->assertEquals('test_value', $savedItem->get());
    }

    public function testDelete(): void
    {
        $this->cache = new Cache(['type' => Cache::TYPE_FILE_SYSTEM, 'prefix' => 'test_prefix_'], $this->cacheDir);
        $item = $this->cache->getItem('test_key');
        $item->set('test_value');
        $this->cache->save($item);

        $this->cache->delete('test_key');
        $deletedItem = $this->cache->getItem('test_key');
        $this->assertFalse($deletedItem->isHit());
    }

    public function testClear(): void
    {
        $this->cache = new Cache(['type' => Cache::TYPE_FILE_SYSTEM, 'prefix' => 'test_prefix_'], $this->cacheDir);
        $item1 = $this->cache->getItem('test_key1');
        $item1->set('test_value1');
        $this->cache->save($item1);

        $item2 = $this->cache->getItem('test_key2');
        $item2->set('test_value2');
        $this->cache->save($item2);

        $this->cache->clear();

        $this->assertFalse($this->cache->getItem('test_key1')->isHit());
        $this->assertFalse($this->cache->getItem('test_key2')->isHit());
    }
}
