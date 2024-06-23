<?php

declare(strict_types=1);

namespace EMS\Helpers\File;

use EMS\Helpers\Standard\Type;
use Psr\Http\Message\StreamInterface;

class TempFile
{
    private const PREFIX = 'EMS_temp_file_';
    private bool $autoClean = false;

    private function __construct(public readonly string $path)
    {
    }

    public function __destruct()
    {
        if ($this->autoClean) {
            $this->clean();
        }
    }

    public static function create(?string $cacheFolder = null): self
    {
        if (!$path = \tempnam($cacheFolder ?? \sys_get_temp_dir(), self::PREFIX)) {
            throw new \RuntimeException(\sprintf('Could not create temp file in "%s"', $cacheFolder ?? \sys_get_temp_dir()));
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

    public function loadFromStream(StreamInterface $stream): self
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

        return $this;
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

    public function setAutoClean(): void
    {
        $this->autoClean = true;
    }

    public function getContents(): string
    {
        $contents = \file_get_contents($this->path);
        if (false === $contents) {
            throw new \RuntimeException('File contents not found');
        }

        return $contents;
    }

    public function getSize(): int
    {
        return Type::integer(\filesize($this->path));
    }
}
