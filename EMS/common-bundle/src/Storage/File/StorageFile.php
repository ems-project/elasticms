<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Storage\File;

use EMS\Helpers\File\TempFile;
use Psr\Http\Message\StreamInterface;

class StorageFile implements FileInterface
{
    private ?TempFile $tempFile = null;

    public function __construct(private readonly StreamInterface $stream)
    {
    }

    public function getContent(): string
    {
        return $this->stream->getContents();
    }

    public function getFilename(): string
    {
        if (null === $this->tempFile) {
            $this->tempFile = TempFile::create();
            $this->tempFile->setAutoClean();
            $this->tempFile->loadFromStream($this->stream);
        }

        return $this->tempFile->path;
    }
}
