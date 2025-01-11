<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\Cache;

use Psr\Cache\CacheItemInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\Adapter\ApcuAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Adapter\RedisAdapter;

final class Cache
{
    private readonly Config $config;

    private ?\Redis $redis = null;
    private ?AdapterInterface $adapter = null;

    public const string TYPE_REDIS = 'redis';
    public const string TYPE_APC = 'apc';
    public const string TYPE_FILE_SYSTEM = 'file_system';

    public const array TYPES = [
        self::TYPE_REDIS,
        self::TYPE_APC,
        self::TYPE_FILE_SYSTEM,
    ];

    /**
     * @param array<mixed> $config
     */
    public function __construct(array $config, private readonly string $cacheDir)
    {
        $this->config = new Config($config);
    }

    public function isApc(): bool
    {
        return self::TYPE_APC === $this->getType();
    }

    public function isRedis(): bool
    {
        return self::TYPE_REDIS === $this->getType();
    }

    public function isFilesystem(): bool
    {
        return self::TYPE_FILE_SYSTEM === $this->getType();
    }

    public function getType(): string
    {
        return $this->config->type;
    }

    public function getPrefix(): string
    {
        return $this->config->prefix;
    }

    public function getItem(string $key): CacheItemInterface
    {
        return $this->getAdapter()->getItem($key);
    }

    public function save(CacheItemInterface $item): void
    {
        $this->getAdapter()->save($item);
    }

    public function getAdapter(): AdapterInterface
    {
        return $this->adapter ?: $this->createAdapter();
    }

    public function getRedis(): \Redis
    {
        return $this->redis ?: $this->createRedis();
    }

    private function createRedis(): \Redis
    {
        if (self::TYPE_REDIS !== $this->getType()) {
            throw new \RuntimeException('Cache type should be redis');
        }

        $config = $this->config->redis;

        $redis = new \Redis();
        $redis->connect($config['host'], $config['port']);

        $this->redis = $redis;

        return $redis;
    }

    private function createAdapter(): AdapterInterface
    {
        $namespace = $this->config->prefix;

        if ($this->isApc()) {
            $adapter = new ApcuAdapter($namespace);
        } elseif ($this->isRedis()) {
            $adapter = new RedisAdapter($this->getRedis(), $namespace);
        } else {
            $adapter = new FilesystemAdapter($namespace, 0, $this->cacheDir);
        }

        $this->adapter = $adapter;

        return $adapter;
    }

    public function delete(string $key): bool
    {
        return $this->getAdapter()->deleteItem($key);
    }

    public function clear(): void
    {
        $this->getAdapter()->clear();
    }
}
