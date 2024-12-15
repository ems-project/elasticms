<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Storage\Service;

use EMS\CommonBundle\Helper\MimeTypeHelper;
use EMS\CommonBundle\Storage\Archive;
use EMS\CommonBundle\Storage\File\FileInterface;
use EMS\CommonBundle\Storage\Processor\Config;
use EMS\CommonBundle\Storage\StreamWrapper;
use GuzzleHttp\Psr7\Stream;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\SplFileInfo;

class FileSystemStorage extends AbstractUrlStorage
{
    public function __construct(LoggerInterface $logger, private readonly string $storagePath, int $usage, int $hotSynchronizeLimit = 0)
    {
        parent::__construct($logger, $usage, $hotSynchronizeLimit);
    }

    protected function getBaseUrl(): string
    {
        return $this->storagePath;
    }

    public function __toString(): string
    {
        return FileSystemStorage::class." ($this->storagePath)";
    }

    /**
     * @return null
     */
    protected function getContext()
    {
        return null;
    }

    public function readCache(Config $config): ?StreamInterface
    {
        $filename = $this->getCachePath($config);
        if (!\file_exists($filename)) {
            return null;
        }
        $resource = \fopen($filename, 'r');
        if (false === $resource) {
            return null;
        }

        return new Stream($resource);
    }

    public function saveCache(Config $config, FileInterface $file): bool
    {
        $filename = $this->getCachePath($config);
        $this->initDirectory($filename);

        return \copy($file->getFilename(), $filename);
    }

    public function clearCache(): bool
    {
        $filesystem = new Filesystem();
        $filesystem->remove(\join(DIRECTORY_SEPARATOR, [
            $this->getBaseUrl(),
            'cache',
        ]));

        return true;
    }

    public function readFromArchiveInCache(string $hash, string $path): ?StreamWrapper
    {
        $filename = \join(DIRECTORY_SEPARATOR, [
            $this->getBaseUrl(),
            'cache',
            \substr($hash, 0, 3),
            \substr($hash, 3),
            $path,
        ]);
        if (!\file_exists($filename)) {
            return null;
        }
        $resource = \fopen($filename, 'r');
        if (false === $resource) {
            return null;
        }
        $mimeTypeHelper = MimeTypeHelper::getInstance();

        return new StreamWrapper(new Stream($resource), $mimeTypeHelper->guessMimeType($filename), \intval(\filesize($filename)));
    }

    public function addFileInArchiveCache(string $hash, SplFileInfo $file, string $mimeType): bool
    {
        $filename = \implode(DIRECTORY_SEPARATOR, [
            $this->getBaseUrl(),
            'cache',
            \substr($hash, 0, 3),
            \substr($hash, 3),
            $file->getRelativePathname(),
        ]);

        $this->initDirectory($filename);

        return \copy($file->getPathname(), $filename);
    }

    public function loadArchiveItemsInCache(string $archiveHash, Archive $archive, ?callable $callback = null): bool
    {
        return false;
    }

    protected function getCachePath(Config $config): string
    {
        return \join(DIRECTORY_SEPARATOR, [
            $this->getBaseUrl(),
            'cache',
            \substr($config->getAssetHash(), 0, 3),
            \substr($config->getAssetHash(), 3),
            \substr($config->getConfigHash(), 0, 3),
            \substr($config->getConfigHash(), 3),
        ]);
    }
}
