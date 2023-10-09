<?php

namespace EMS\Tests\CommonBundle\Unit\Common\Cache;

use EMS\CommonBundle\Common\Cache\Cache;
use EMS\CommonBundle\Common\Cache\Config;
use PHPUnit\Framework\TestCase;

class ConfigAiTest extends TestCase
{
    public function testValidConfig(): void
    {
        $config = new Config([
            'type' => Cache::TYPE_REDIS,
            'prefix' => 'test_prefix_',
            'redis' => [
                'host' => 'localhost',
                'port' => 6379,
            ],
        ]);

        $this->assertEquals(Cache::TYPE_REDIS, $config->type);
        $this->assertEquals('test_prefix_', $config->prefix);
        $this->assertEquals('localhost', $config->redis['host']);
        $this->assertEquals(6379, $config->redis['port']);
    }

    public function testDefaultRedisConfig(): void
    {
        $config = new Config([
            'type' => Cache::TYPE_REDIS,
            'prefix' => 'test_prefix_',
        ]);

        $this->assertEquals('localhost', $config->redis['host']);
        $this->assertEquals(6379, $config->redis['port']);
    }

    public function testInvalidType(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Config([
            'type' => 'invalid_type',
            'prefix' => 'test_prefix_',
        ]);
    }

    public function testMissingRequiredFields(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Config([
            'type' => Cache::TYPE_REDIS,
        ]);
    }

    public function testInvalidRedisPort(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Config([
            'type' => Cache::TYPE_REDIS,
            'prefix' => 'test_prefix_',
            'redis' => [
                'host' => 'localhost',
                'port' => 'invalid_port',
            ],
        ]);
    }
}
