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

    private static function create(?string $cacheFolder = null): self
    {
        if (!$path = \tempnam($cacheFolder ?? \sys_get_temp_dir(), self::PREFIX)) {
            throw new \RuntimeException(\sprintf('Could not create temp file in "%s"', \sys_get_temp_dir()));
        }

        return new self($path);
    }

    public static function createNamed(string $name, ?string $cacheFolder = null): self
    {
        return new self(\implode(\DIRECTORY_SEPARATOR, [$cacheFolder ?? \sys_get_temp_dir(), self::PREFIX.$name]));
    }

    public function exists(): bool
    {
        return \file_exists($this->path);
    }

    public static function fromStream(StreamInterface $stream, ?string $name = null, ?string $cacheFolder = null): self
    {
        $tempFile = $name ? self::createNamed($name) : self::create($cacheFolder);
        if (!$tempFile->exists()) {
            $tempFile->loadFromStream($stream);
        }

        return $tempFile;
    }

    public function loadFromStream(StreamInterface $stream): void
    {
        if (!$handle = \fopen($this->path, 'w')) {
            throw new \RuntimeException(\sprintf('Can\'t open a temporary file %s', $this->path));
        }

        while (!$stream->eof()) {
            if (false === \fwrite($handle, $stream->read(8192))) {
                throw new \RuntimeException(\sprintf('Can\'t write in temporary file %s', $this->path));
            }
        }

        if (false === \fclose($handle)) {
            throw new \RuntimeException(\sprintf('Can\'t close the temporary file %s', $this->path));
        }
    }

    public function clean(): void
    {
        if (!$this->exists()) {
            return;
        }
        try {
            @\unlink($this->path);
        } catch (\Throwable) {
        }
    }
}
