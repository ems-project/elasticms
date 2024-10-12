<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Storage;

class ArchiveItem implements \JsonSerializable
{
    public const FILENAME = 'filename';
    public const HASH = 'hash';
    public const TYPE = 'type';
    public const SIZE = 'size';

    public function __construct(
        private readonly string $filename,
        private readonly string $type,
        private readonly int $size,
        private readonly string $hash
    ) {
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * @return array{filename: string, hash: string, type: string, size: int}
     */
    public function jsonSerialize(): array
    {
        return [
            self::FILENAME => $this->filename,
            self::HASH => $this->hash,
            self::TYPE => $this->type,
            self::SIZE => $this->size,
        ];
    }
}
