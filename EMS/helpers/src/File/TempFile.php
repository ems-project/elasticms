<?php

declare(strict_types=1);

namespace EMS\Helpers\File;

use EMS\Helpers\Standard\Type;
use Psr\Http\Message\StreamInterface;

class TempFile
{
    public static ?self $preventDestruct = null;
    private const string PREFIX = 'EMS_temp_file_';

    private function __construct(public readonly string $path)
    {
        self::$preventDestruct = $this;
    }

    public function __destruct()
    {
        $this->clean();
    }

    public static function create(): self
    {
        if (!$path = \tempnam(\sys_get_temp_dir(), self::PREFIX)) {
            throw new \RuntimeException(\sprintf('Could not create temp file in "%s"', \sys_get_temp_dir()));
        }

        return new self($path);
    }

    public function exists(): bool
    {
        return \file_exists($this->path);
    }

    public function loadFromStream(StreamInterface $stream, ?callable $callback = null): self
    {
        if (!$handle = \fopen($this->path, 'w')) {
            throw new \RuntimeException(\sprintf('Can\'t open a temporary file %s', $this->path));
        }

        while (!$stream->eof()) {
            $size = \fwrite($handle, $stream->read(File::DEFAULT_CHUNK_SIZE));
            if (false === $size) {
                throw new \RuntimeException(\sprintf('Can\'t write in temporary file %s', $this->path));
            }
            if (null !== $callback) {
                $callback($size);
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
