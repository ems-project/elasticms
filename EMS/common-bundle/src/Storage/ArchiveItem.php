<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Storage;

class ArchiveItem
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
}
