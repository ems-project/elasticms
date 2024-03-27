<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Storage\File;

use EMS\CommonBundle\Common\Standard\Type;

class LocalFile implements FileInterface
{
    public function __construct(private readonly string $filename)
    {
    }

    public function getContent(): string
    {
        return Type::string(\file_get_contents($this->filename));
    }

    public function close(): void
    {
    }

    public function getFilename(): string
    {
        return $this->filename;
    }
}
