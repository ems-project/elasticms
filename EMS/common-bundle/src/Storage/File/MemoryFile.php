<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Storage\File;

class MemoryFile implements FileInterface
{
    public function __construct(private string $filename, private string $content)
    {
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }
}
