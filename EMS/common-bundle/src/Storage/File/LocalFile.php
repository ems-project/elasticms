<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Storage\File;

use EMS\Helpers\File\TempFile;
use EMS\Helpers\Standard\Type;

class LocalFile implements FileInterface
{
    private ?TempFile $tempFile = null;

    public function __construct(private readonly string $filename)
    {
    }

    public static function fromTempFile(TempFile $tempFile): self
    {
        $localFile = new self($tempFile->path);
        $localFile->tempFile = $tempFile;

        return $localFile;
    }

    #[\Override]
    public function getContent(): string
    {
        return Type::string(\file_get_contents($this->filename));
    }

    #[\Override]
    public function getFilename(): string
    {
        return $this->filename;
    }

    public function getTempFile(): ?TempFile
    {
        return $this->tempFile;
    }
}
