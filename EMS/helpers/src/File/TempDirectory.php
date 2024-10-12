<?php

declare(strict_types=1);

namespace EMS\Helpers\File;

use Symfony\Component\Filesystem\Filesystem;

class TempDirectory
{
    private const PREFIX = 'EMS_temp_dir_';
    private Filesystem $filesystem;
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

    public static function createFromZipArchive(TempFile $zipFile): self
    {
        $tempDir = self::create();
        $zip = new \ZipArchive();
        if (true !== $open = $zip->open($zipFile->path)) {
            throw new \RuntimeException(\sprintf('Failed opening zip %s (ZipArchive %s)', $zipFile->path, $open));
        }

        if (!$zip->extractTo($tempDir->path)) {
            throw new \RuntimeException(\sprintf('Extracting of zip file failed (%s)', $tempDir->path));
        }
        $zip->close();
        $zipFile->clean();

        return $tempDir;
    }
}
