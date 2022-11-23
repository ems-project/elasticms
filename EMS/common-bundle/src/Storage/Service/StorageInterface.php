<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Storage\Service;

use Psr\Http\Message\StreamInterface;

interface StorageInterface
{
    /** @var int */
    public const STORAGE_USAGE_CACHE = 0;
    /** @var int */
    public const STORAGE_USAGE_CONFIG = 1;
    /** @var int */
    public const STORAGE_USAGE_ASSET = 2;
    /** @var int */
    public const STORAGE_USAGE_BACKUP = 3;
    /** @var int */
    public const STORAGE_USAGE_EXTERNAL = 4;
    /** @var string */
    public const STORAGE_USAGE_CACHE_ATTRIBUTE = 'cache';
    /** @var string */
    public const STORAGE_USAGE_CONFIG_ATTRIBUTE = 'config';
    /** @var string */
    public const STORAGE_USAGE_ASSET_ATTRIBUTE = 'asset';
    /** @var string */
    public const STORAGE_USAGE_BACKUP_ATTRIBUTE = 'backup';
    /** @var string */
    public const STORAGE_USAGE_EXTERNAL_ATTRIBUTE = 'external';

    /** @var array<string, int> */
    public const STORAGE_USAGES = [
        self::STORAGE_USAGE_CACHE_ATTRIBUTE => self::STORAGE_USAGE_CACHE,
        self::STORAGE_USAGE_CONFIG_ATTRIBUTE => self::STORAGE_USAGE_CONFIG,
        self::STORAGE_USAGE_ASSET_ATTRIBUTE => self::STORAGE_USAGE_ASSET,
        self::STORAGE_USAGE_BACKUP_ATTRIBUTE => self::STORAGE_USAGE_BACKUP,
        self::STORAGE_USAGE_EXTERNAL_ATTRIBUTE => self::STORAGE_USAGE_EXTERNAL,
    ];

    public function head(string $hash): bool;

    public function health(): bool;

    public function __toString(): string;

    public function create(string $hash, string $filename): bool;

    public function read(string $hash, bool $confirmed = true): StreamInterface;

    public function getSize(string $hash): int;

    public function remove(string $hash): bool;

    public function initUpload(string $hash, int $size, string $name, string $type): bool;

    public function addChunk(string $hash, string $chunk): bool;

    public function finalizeUpload(string $hash): bool;

    public function getUsage(): int;

    public function getHotSynchronizeLimit(): int;

    public function removeUpload(string $hash): void;
}
