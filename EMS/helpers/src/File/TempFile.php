<?php

declare(strict_types=1);

namespace EMS\Helpers\File;

use Psr\Http\Message\StreamInterface;

class TempFile
{
    public string $path;

    private function __construct(string $name)
    {
        $this->path = \implode(\DIRECTORY_SEPARATOR, [\sys_get_temp_dir(), 'EMS_temp_file_'.$name]);
    }

    private function exists(): bool
    {
        return \file_exists($this->path);
    }

    public static function fromStream(StreamInterface $stream, string $name): self
    {
        $tempFile = new self($name);

        if ($tempFile->exists()) {
            return $tempFile;
        }

        if (!$handle = \fopen($tempFile->path, 'w')) {
            throw new \RuntimeException(\sprintf('Can\'t open a temporary file %s', $tempFile->path));
        }

        while (!$stream->eof()) {
            if (false === \fwrite($handle, $stream->read(8192))) {
                throw new \RuntimeException(\sprintf('Can\'t write in temporary file %s', $tempFile->path));
            }
        }

        if (false === \fclose($handle)) {
            throw new \RuntimeException(\sprintf('Can\'t close the temporary file %s', $tempFile->path));
        }

        return $tempFile;
    }
}
