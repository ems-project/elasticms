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
        public readonly string $filename,
        public readonly string $type,
        public readonly int $size,
        public readonly string $hash,
    ) {
    }

    /**
     * @return array{filename: string, hash: string, type: string, size: int}
     */
    public function jsonSerialize(): array
    {
        return [
            ArchiveItem::FILENAME => $this->filename,
            ArchiveItem::HASH => $this->hash,
            ArchiveItem::TYPE => $this->type,
            ArchiveItem::SIZE => $this->size,
        ];
    }
}
