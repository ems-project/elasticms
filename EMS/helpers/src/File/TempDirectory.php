<?php

declare(strict_types=1);

namespace EMS\Helpers\File;

use Psr\Http\Message\StreamInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class TempDirectory
{
    private const string PREFIX = 'EMS_temp_dir_';
    private readonly Filesystem $filesystem;
    /** @var self[] */
    private static array $collector = [];

    private function __construct(public readonly string $path)
    {
        $this->filesystem = new Filesystem();
        $this->filesystem->remove($this->path);
        $this->filesystem->mkdir($this->path);
        self::$collector[] = $this;
    }

    public function __destruct()
    {
        $this->filesystem->remove($this->path);
    }

    /**
     * @return self[]
     */
    public static function getIterator(): array
    {
        return self::$collector;
    }

    public static function create(): self
    {
        if (!$path = \tempnam(\sys_get_temp_dir(), self::PREFIX)) {
            throw new \RuntimeException(\sprintf('Could not create temp directory in "%s"', \sys_get_temp_dir()));
        }

        return new self($path);
    }

    public function exists(): bool
    {
        return \is_dir($this->path);
    }

    public static function createFromZipArchive(string $zipFile): self
    {
        $tempDir = self::create();
        $zip = new \ZipArchive();
        if (true !== $open = $zip->open($zipFile)) {
            throw new \RuntimeException(\sprintf('Failed opening zip %s (ZipArchive %s)', $zipFile, $open));
        }

        if (!$zip->extractTo($tempDir->path)) {
            throw new \RuntimeException(\sprintf('Extracting of zip file failed (%s)', $tempDir->path));
        }
        $zip->close();

        return $tempDir;
    }

    public function touch(string $hash): void
    {
        $this->filesystem->touch($this->path.\DIRECTORY_SEPARATOR.$hash);
    }

    public function moveTo(string $directory): void
    {
        $this->filesystem->mkdir($directory);
        $finder = Finder::create();
        foreach ($finder->in($this->path)->depth('< 1') as $file) {
            $this->filesystem->rename($file->getPathname(), $directory.\DIRECTORY_SEPARATOR.$file->getRelativePathname());
        }
    }

    public function add(StreamInterface $stream, string $filename): void
    {
        $explodedPath = \explode(\DIRECTORY_SEPARATOR, $this->path.\DIRECTORY_SEPARATOR.$filename);
        \array_pop($explodedPath);
        $this->filesystem->mkdir(\implode(\DIRECTORY_SEPARATOR, $explodedPath));
        if (!$handle = \fopen($this->path.\DIRECTORY_SEPARATOR.$filename, 'w')) {
            throw new \RuntimeException(\sprintf('Can\'t open a temporary file %s', $this->path));
        }

        while (!$stream->eof()) {
            if (false === \fwrite($handle, $stream->read(File::DEFAULT_CHUNK_SIZE))) {
                throw new \RuntimeException(\sprintf('Can\'t write in temporary file %s', $this->path));
            }
        }

        if (false === \fclose($handle)) {
            throw new \RuntimeException(\sprintf('Can\'t close the temporary file %s', $this->path));
        }
    }
}
