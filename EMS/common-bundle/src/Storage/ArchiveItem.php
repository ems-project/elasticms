<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Storage;

class ArchiveItem implements \JsonSerializable
{
    public function __construct(private readonly string $filename, private readonly string $type, private readonly string $hash)
    {
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

    /**
     * @return array{filename: string, hash: string, type: string}
     */
    public function jsonSerialize(): array
    {
        return [
            'filename' => $this->filename,
            'hash' => $this->hash,
            'type' => $this->type,
        ];
    }
}
