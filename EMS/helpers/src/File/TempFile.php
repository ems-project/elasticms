<?php

declare(strict_types=1);

namespace EMS\Helpers\File;

use Psr\Http\Message\StreamInterface;

class TempFile
{
    private const PREFIX = 'EMS_temp_file_';

    private function __construct(public readonly string $path)
    {
    }

    private static function create(): self
    {
        if (!$path = \tempnam(\sys_get_temp_dir(), self::PREFIX)) {
            throw new \RuntimeException(\sprintf('Could not create temp file in "%s"', \sys_get_temp_dir()));
        }

        return new self($path);
    }

    private static function createNamed(string $name): self
    {
        return new self(\implode(\DIRECTORY_SEPARATOR, [\sys_get_temp_dir(), self::PREFIX.$name]));
    }

    public function exists(): bool
    {
        return \file_exists($this->path);
    }

    public static function fromStream(StreamInterface $stream, ?string $name = null): self
    {
        $tempFile = $name ? self::createNamed($name) : self::create();

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
