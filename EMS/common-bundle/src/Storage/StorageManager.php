<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Storage;

use EMS\CommonBundle\Contracts\File\FileManagerInterface;
use EMS\CommonBundle\Helper\EmsFields;
use EMS\CommonBundle\Helper\MimeTypeHelper;
use EMS\CommonBundle\Storage\Factory\StorageFactoryInterface;
use EMS\CommonBundle\Storage\File\FileInterface;
use EMS\CommonBundle\Storage\File\LocalFile;
use EMS\CommonBundle\Storage\File\StorageFile;
use EMS\CommonBundle\Storage\Processor\Config;
use EMS\CommonBundle\Storage\Service\StorageInterface;
use EMS\Helpers\File\File;
use EMS\Helpers\File\File as FileHelper;
use EMS\Helpers\File\TempDirectory;
use EMS\Helpers\File\TempFile;
use EMS\Helpers\Html\MimeTypes;
use EMS\Helpers\Standard\Json;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class StorageManager implements FileManagerInterface
{
    /** @var StorageInterface[] */
    private array $adapters = [];
    /** @var StorageFactoryInterface[] */
    private array $factories = [];
    /**
     * @var int<1, max>
     */
    private int $headChunkSize = FileManagerInterface::HEADS_CHUNK_SIZE;

    /**
     * @param iterable<StorageFactoryInterface>                                            $factories
     * @param array<array{type?: string, url?: string, required?: bool, read-only?: bool}> $storageConfigs
     */
    public function __construct(private readonly LoggerInterface $logger, private readonly FileLocatorInterface $fileLocator, iterable $factories, private readonly string $hashAlgo, private readonly array $storageConfigs = [])
    {
        foreach ($factories as $factory) {
            if (!$factory instanceof StorageFactoryInterface) {
                throw new \RuntimeException('Unexpected StorageInterface class');
            }
            $this->addStorageFactory($factory);
        }
        $this->registerServicesFromConfigs();
    }

    private function addStorageFactory(StorageFactoryInterface $factory): void
    {
        $this->factories[$factory->getStorageType()] = $factory;
    }

    private function registerServicesFromConfigs(): void
    {
        foreach ($this->storageConfigs as $storageConfig) {
            $type = $storageConfig['type'] ?? null;
            if (null === $type) {
                $this->logger->error('Storage type not defined.');
                continue;
            }
            $factory = $this->factories[$type] ?? null;
            if (null === $factory) {
                $this->logger->error(\sprintf('Storage factory "%s" was not found.', $factory));
                continue;
            }
            $storage = $factory->createService($storageConfig);
            if (null !== $storage) {
                $this->addAdapter($storage);
            }
        }
    }

    public function addAdapter(StorageInterface $storageAdapter): StorageManager
    {
        $this->adapters[] = $storageAdapter;

        return $this;
    }

    public function head(string $hash): bool
    {
        foreach ($this->adapters as $adapter) {
            if ($adapter->head($hash)) {
                return true;
            }
        }

        return false;
    }

    #[\Override]
    public function heads(string ...$fileHashes): \Traversable
    {
        $uniqueFileHashes = \array_unique($fileHashes);
        $pagedHashes = \array_chunk($uniqueFileHashes, $this->headChunkSize, true);

        foreach ($pagedHashes as $hashes) {
            foreach ($this->adapters as $adapter) {
                yield from $adapter->heads(...$hashes);
                break;
            }
        }
    }

    /**
     * @return string[]
     */
    public function headIn(string $hash): array
    {
        $storages = [];
        foreach ($this->adapters as $adapter) {
            if ($adapter->head($hash)) {
                $storages[] = $adapter->__toString();
            }
        }

        return $storages;
    }

    #[\Override]
    public function getStream(string $hash): StreamInterface
    {
        /** @var StorageInterface[] $missingIn */
        $missingIn = [];

        foreach ($this->adapters as $adapter) {
            if ($adapter->head($hash)) {
                try {
                    $this->hotSynchronize($hash, $adapter, $missingIn);

                    return $adapter->read($hash);
                } catch (\Throwable) {
                    continue;
                }
            } else {
                $missingIn[] = $adapter;
            }
        }
        throw new NotFoundException($hash);
    }

    #[\Override]
    public function getContents(string $hash): string
    {
        return $this->getStream($hash)->getContents();
    }

    /**
     * @return array<string, mixed>
     */
    public function getConfig(string $hash): mixed
    {
        return Json::decode($this->getStream($hash)->getContents());
    }

    public function getPublicImage(string $name): string
    {
        return $this->fileLocator->locate('@EMSCommonBundle/Resources/public/images/'.$name);
    }

    #[\Override]
    public function getHashAlgo(): string
    {
        return $this->hashAlgo;
    }

    public function saveContents(string $contents, string $filename, string $mimetype, int $usageType): string
    {
        $hash = $this->computeStringHash($contents);
        $count = 0;
        foreach ($this->adapters as $adapter) {
            try {
                if ($count > 0 && $usageType < StorageInterface::STORAGE_USAGE_ASSET) {
                    break;
                }

                if (!$this->isUsageSupported($adapter, $usageType)) {
                    continue;
                }

                if ($adapter->head($hash)) {
                    ++$count;
                    continue;
                }

                if (!$adapter->initUpload($hash, \strlen($contents), $filename, $mimetype)) {
                    continue;
                }

                if (!$adapter->addChunk($hash, $contents)) {
                    continue;
                }

                $adapter->initFinalize($hash);

                if ($adapter->finalizeUpload($hash)) {
                    ++$count;
                }
            } catch (\Throwable $e) {
                $this->logger->error(\sprintf('Not able to save %s in %s with message: %s', $hash, $adapter->__toString(), $e->getMessage()));
            }
        }

        if (0 === $count) {
            $this->logger->error(\sprintf('The config %s was not able to be saved', $hash));
            if ($usageType >= StorageInterface::STORAGE_USAGE_ASSET) {
                throw new NotSavedException($hash);
            }
        }

        return $hash;
    }

    public function computeStringHash(string $string, ?string $hashAlgo = null, bool $binary = false): string
    {
        return \hash($hashAlgo ?? $this->hashAlgo, $string, $binary);
    }

    public function computeFileHash(string $filename): string
    {
        $hashFile = \hash_file($this->hashAlgo, $filename);
        if (false === $hashFile) {
            throw new NotFoundException($filename);
        }

        return $hashFile;
    }

    public function computeStreamHash(StreamInterface $handler): string
    {
        if (0 !== $handler->tell()) {
            $handler->rewind();
        }
        $hashContext = \hash_init($this->hashAlgo);
        while (!$handler->eof()) {
            \hash_update($hashContext, $handler->read(File::DEFAULT_CHUNK_SIZE));
        }

        return \hash_final($hashContext);
    }

    public function initUploadFile(string $fileHash, int $fileSize, string $fileName, string $mimeType, int $usageType): int
    {
        $count = 0;
        foreach ($this->adapters as $adapter) {
            if (!$this->isUsageSupported($adapter, $usageType)) {
                continue;
            }
            if ($adapter->initUpload($fileHash, $fileSize, $fileName, $mimeType)) {
                ++$count;
            }
        }

        if (0 === $count) {
            throw new \RuntimeException(\sprintf('Impossible to initiate the upload of an asset identified by the hash %s into at least one storage services', $fileHash));
        }

        return $count;
    }

    public function addChunk(string $hash, string $chunk, int $usageType): int
    {
        $count = 0;
        foreach ($this->adapters as $adapter) {
            if (!$this->isUsageSupported($adapter, $usageType)) {
                continue;
            }
            if ($adapter->addChunk($hash, $chunk)) {
                ++$count;
            }
        }

        if (0 === $count) {
            throw new \RuntimeException(\sprintf('Impossible to add a chunk of an asset identified by the hash %s into at least one storage services', $hash));
        }

        return $count;
    }

    /**
     * @return array<string, bool>
     */
    public function getHealthStatuses(): array
    {
        $statuses = [];
        foreach ($this->adapters as $adapter) {
            $statuses[$adapter->__toString()] = $adapter->health();
        }

        return $statuses;
    }

    public function getSize(string $hash): int
    {
        foreach ($this->adapters as $adapter) {
            try {
                return $adapter->getSize($hash);
            } catch (\Throwable) {
                continue;
            }
        }
        throw new NotFoundException($hash);
    }

    public function getBase64(string $hash): ?string
    {
        foreach ($this->adapters as $adapter) {
            try {
                $stream = $adapter->read($hash);
            } catch (\Throwable) {
                continue;
            }

            return \base64_encode($stream->getContents());
        }

        return null;
    }

    public function finalizeUpload(string $hash, int $size, int $usageType): int
    {
        $count = 0;
        foreach ($this->adapters as $adapter) {
            if (!$this->isUsageSupported($adapter, $usageType)) {
                continue;
            }

            try {
                $adapter->initFinalize($hash);
                $handler = $adapter->read($hash, false);
            } catch (\Throwable) {
                continue;
            }

            $uploadedSize = $handler->getSize();
            if (null === $uploadedSize) {
                continue;
            }
            $computedHash = $this->computeStreamHash($handler);

            if ($computedHash !== $hash) {
                $adapter->removeUpload($hash);
                throw new HashMismatchException($hash, $computedHash);
            }

            if ($uploadedSize !== $size) {
                $adapter->removeUpload($hash);
                throw new SizeMismatchException($hash, $size, $uploadedSize);
            }

            if ($adapter->finalizeUpload($hash)) {
                ++$count;
            }
        }

        if (0 === $count) {
            throw new \RuntimeException(\sprintf('Impossible finalize the upload of an asset identified by the hash %s into at least one storage services', $hash));
        }

        return $count;
    }

    public function saveFile(string $filename, int $usageType): string
    {
        $count = 0;
        $hash = $this->computeFileHash($filename);
        foreach ($this->adapters as $adapter) {
            if (!$this->isUsageSupported($adapter, $usageType)) {
                continue;
            }
            if ($adapter->create($hash, $filename)) {
                ++$count;
            }
        }

        if (0 === $count) {
            throw new NotSavedException($hash);
        }

        return $hash;
    }

    public function remove(string $hash): int
    {
        $count = 0;
        foreach ($this->adapters as $adapter) {
            if (!$this->isUsageSupported($adapter, StorageInterface::STORAGE_USAGE_BACKUP)) {
                continue;
            }
            try {
                if ($adapter->remove($hash)) {
                    ++$count;
                }
            } catch (\Throwable) {
                continue;
            }
        }

        return $count;
    }

    /**
     * @param array<string, mixed> $config
     */
    public function saveConfig(array $config, int $usageType = StorageInterface::STORAGE_USAGE_CONFIG): string
    {
        if (\is_array($config[EmsFields::ASSET_CONFIG_FILE_NAMES] ?? null) && \count($config[EmsFields::ASSET_CONFIG_FILE_NAMES]) > 0) {
            $hashContext = \hash_init('sha1');
            foreach ($config[EmsFields::ASSET_CONFIG_FILE_NAMES] as $filename) {
                if (!\file_exists($filename)) {
                    continue;
                }
                $handle = \fopen($filename, 'rb');
                if (false === $handle) {
                    continue;
                }

                while (!\feof($handle)) {
                    $data = \fread($handle, 8192);
                    if (false === $data) {
                        throw new \RuntimeException('Unexpected false data');
                    }
                    \hash_update($hashContext, $data);
                }
                \fclose($handle);
                break;
            }
            $config[EmsFields::ASSET_SEED] = \hash_final($hashContext);
        }
        Json::normalize($config);
        $normalizedArray = Json::encode($config);

        return $this->saveContents($normalizedArray, 'assetConfig.json', 'application/json', $usageType);
    }

    #[\Override]
    public function getFile(string $filenameOrHash): FileInterface
    {
        if (\file_exists($filenameOrHash)) {
            return new LocalFile($filenameOrHash);
        }
        if (1 === \preg_match('/[0-9a-fA-F]/', $filenameOrHash)) {
            return new StorageFile($this->getStream($filenameOrHash));
        }

        throw new \RuntimeException(\sprintf('File %s not found', $filenameOrHash));
    }

    private function isUsageSupported(StorageInterface $adapter, int $usageRequested): bool
    {
        if ($adapter->getUsage() >= StorageInterface::STORAGE_USAGE_EXTERNAL) {
            return false;
        }

        return $usageRequested >= $adapter->getUsage();
    }

    /**
     * @param StorageInterface[] $missingIn
     */
    private function hotSynchronize(string $hash, StorageInterface $source, array $missingIn): void
    {
        if (empty($missingIn)) {
            return;
        }
        try {
            $size = $this->getSize($hash);
            $filteredAdapters = [];
            foreach ($missingIn as $adapter) {
                if ($size < $adapter->getHotSynchronizeLimit()) {
                    $filteredAdapters[] = $adapter;
                }
            }

            if (empty($filteredAdapters)) {
                return;
            }

            foreach ($filteredAdapters as $adapter) {
                $adapter->initUpload($hash, $size, 'hotSynchronized', 'application/bin');
            }

            $stream = $source->read($hash);
            while (!$stream->eof()) {
                $chunk = $stream->read(4096);
                foreach ($filteredAdapters as $adapter) {
                    $adapter->addChunk($hash, $chunk);
                }
            }

            foreach ($filteredAdapters as $adapter) {
                $adapter->initFinalize($hash);
                $adapter->finalizeUpload($hash);
            }
        } catch (\Throwable $e) {
            $this->logger->warning(\sprintf('It was not possible to hot synchronize the asset %s: %s', $hash, $e->getMessage()));
        }
    }

    public function clearCaches(): int
    {
        $count = 0;
        foreach ($this->adapters as $adapter) {
            $count += $adapter->clearCache() ? 1 : 0;
        }

        return $count;
    }

    public function readCache(Config $config): ?StreamInterface
    {
        foreach ($this->adapters as $adapter) {
            $stream = $adapter->readCache($config);
            if (null !== $stream) {
                return $stream;
            }
        }

        return null;
    }

    public function saveCache(Config $config, FileInterface $file): void
    {
        foreach ($this->adapters as $adapter) {
            if ($adapter->saveCache($config, $file)) {
                return;
            }
        }
    }

    public function getStreamFromArchive(string $hash, string $path, bool $extract = true, ?string $indexResource = null): StreamWrapper
    {
        if (null !== $indexResource && ('' === $path || \str_ends_with($path, '/'))) {
            $path .= $indexResource;
        }
        foreach ($this->adapters as $adapter) {
            $stream = $adapter->readFromArchiveInCache($hash, $path);
            if (null !== $stream) {
                return $stream;
            }
        }
        if (!$extract) {
            throw new NotFoundHttpException(\sprintf('File %s not found', $path));
        }
        $this->logger->debug(\sprintf('File %s from archive %s is not in cache', $path, $hash));

        if (!$this->head($hash)) {
            throw new NotFoundHttpException(\sprintf('Archive %s not found', $hash));
        }

        $archiveFile = TempFile::create()->loadFromStream($this->getStream($hash));
        $mimeType = MimeTypeHelper::getInstance()->guessMimeType($archiveFile->path);

        return match ($mimeType) {
            MimeTypes::APPLICATION_ZIP->value, MimeTypes::APPLICATION_GZIP->value => $this->getStreamFromZipArchive($hash, $path, $archiveFile),
            MimeTypes::APPLICATION_JSON->value => $this->getStreamFromJsonArchive($hash, $path, $archiveFile),
            default => throw new \RuntimeException(\sprintf('Archive format %s not supported', $mimeType)),
        };
    }

    public function extractFromArchive(string $hash): TempDirectory
    {
        $archiveFile = TempFile::create()->loadFromStream($this->getStream($hash));
        $type = MimeTypeHelper::getInstance()->guessMimeType($archiveFile->path);
        switch ($type) {
            case MimeTypes::APPLICATION_ZIP->value:
            case MimeTypes::APPLICATION_GZIP->value:
                $tempDir = TempDirectory::createFromZipArchive($archiveFile->path);
                break;
            case MimeTypes::APPLICATION_JSON->value:
                $archive = Archive::fromStructure($archiveFile->getContents(), $this->hashAlgo);
                $tempDir = TempDirectory::create();
                foreach ($archive->iterator() as $file) {
                    $tempDir->add($this->getStream($file->hash), $file->filename);
                }
                break;
            default:
                throw new \RuntimeException(\sprintf('Archive format %s not supported', $type));
        }
        $tempDir->touch($hash);
        $archiveFile->clean();

        return $tempDir;
    }

    private function getStreamFromZipArchive(string $hash, string $path, TempFile $zipFile): StreamWrapper
    {
        $dir = TempDirectory::createFromZipArchive($zipFile->path);
        $zipFile->clean();
        $finder = new Finder();
        $finder->in($dir->path)->files();
        $counter = 0;
        $mimeTypeHelper = MimeTypeHelper::getInstance();
        /** @var SplFileInfo $file */
        foreach ($finder as $file) {
            $mimeType = $mimeTypeHelper->guessMimeType($file->getPathname());
            foreach ($this->adapters as $adapter) {
                if ($adapter->addFileInArchiveCache($hash, $file, $mimeType)) {
                    ++$counter;
                    break;
                }
            }
        }
        if ($finder->count() === $counter) {
            $this->logger->debug(\sprintf('%d files have been successfully saved in cache', $counter));
        } elseif (0 === $counter) {
            $this->logger->warning(\sprintf('None of the %d files have been successfully saved in cache', $finder->count()));
        } else {
            $this->logger->warning(\sprintf('%d files, on a total of %d, have been successfully saved in cache', $counter, $finder->count()));
        }

        $filename = \implode(DIRECTORY_SEPARATOR, [$dir->path, $path]);
        if (!\file_exists($filename)) {
            throw new NotFoundHttpException(\sprintf('File %s not found in archive %s', $path, $hash));
        }
        $file = File::fromFilename($filename);
        $mimeTypeHelper = MimeTypeHelper::getInstance();

        return new StreamWrapper($file->getStream(), $mimeTypeHelper->guessMimeType($filename), $file->getSize());
    }

    private function getStreamFromJsonArchive(string $hash, string $path, TempFile $archiveFile): StreamWrapper
    {
        $archive = Archive::fromStructure($archiveFile->getContents(), $this->hashAlgo);
        $file = $archive->getByPath($path);
        if (null === $file) {
            throw new NotFoundHttpException(\sprintf('File %s not found in archive %s', $path, $hash));
        }
        $counter = 0;
        foreach ($this->adapters as $adapter) {
            if ($adapter->loadArchiveItemsInCache($hash, $archive)) {
                ++$counter;
                break;
            }
        }
        if ($archive->getCount() === $counter) {
            $this->logger->debug(\sprintf('%d files have been successfully saved in cache', $counter));
        } elseif (0 === $counter) {
            $this->logger->warning(\sprintf('None of the %d files have been successfully saved in cache', $archive->getCount()));
        } else {
            $this->logger->warning(\sprintf('%d files, on a total of %d, have been successfully saved in cache', $counter, $archive->getCount()));
        }

        return new StreamWrapper($this->getStream($file->hash), $file->type, $file->size);
    }

    #[\Override]
    public function uploadFile(string $realPath, ?string $mimeType = null, ?string $filename = null, ?callable $callback = null): string
    {
        $fileHash = $this->computeFileHash($realPath);

        if ($this->head($fileHash)) {
            return $fileHash;
        }

        $file = FileHelper::fromFilename($realPath);
        $mimeType ??= $file->mimeType;
        $filename ??= $file->name;

        $this->initUploadFile(
            fileHash: $fileHash,
            fileSize: $file->size,
            fileName: $filename,
            mimeType: $mimeType,
            usageType: StorageInterface::STORAGE_USAGE_ASSET
        );

        foreach ($file->chunk(0) as $chunk) {
            $this->addChunk($fileHash, $chunk, StorageInterface::STORAGE_USAGE_ASSET);
            if (null !== $callback) {
                $callback($chunk);
            }
        }

        $this->finalizeUpload($fileHash, $file->size, StorageInterface::STORAGE_USAGE_ASSET);

        return $fileHash;
    }

    #[\Override]
    public function uploadContents(string $contents, string $filename, string $mimeType): string
    {
        return $this->saveContents(
            contents: $contents,
            filename: $filename,
            mimetype: $mimeType,
            usageType: StorageInterface::STORAGE_USAGE_ASSET
        );
    }

    #[\Override]
    public function downloadFile(string $hash): string
    {
        return $this->getFile($hash)->getFilename();
    }

    /**
     * @param int<1, max> $chunkSize
     */
    #[\Override]
    public function setHeadChunkSize(int $chunkSize): void
    {
        $this->headChunkSize = $chunkSize;
    }

    #[\Override]
    public function loadArchiveItemsInCache(string $archiveHash, Archive $archive, ?callable $callback = null): void
    {
        foreach ($this->adapters as $adapter) {
            if ($adapter->loadArchiveItemsInCache($archiveHash, $archive, $callback)) {
                break;
            }
        }
    }

    public function addFileInArchiveCache(string $hash, SplFileInfo $file, string $mimeType): bool
    {
        foreach ($this->adapters as $adapter) {
            if ($adapter->addFileInArchiveCache($hash, $file, $mimeType)) {
                return true;
            }
        }

        return false;
    }
}
