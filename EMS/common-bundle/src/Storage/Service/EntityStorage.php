<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Storage\Service;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Persistence\ObjectManager;
use EMS\CommonBundle\Entity\AssetStorage;
use EMS\CommonBundle\Repository\AssetStorageRepository;
use EMS\CommonBundle\Storage\Archive;
use EMS\CommonBundle\Storage\File\FileInterface;
use EMS\CommonBundle\Storage\Processor\Config;
use EMS\CommonBundle\Storage\StreamWrapper;
use GuzzleHttp\Psr7\Stream;
use Psr\Http\Message\StreamInterface;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class EntityStorage implements StorageInterface, \Stringable
{
    private readonly ObjectManager $manager;
    private readonly AssetStorageRepository $repository;

    public function __construct(Registry $doctrine, private readonly int $usage, private readonly int $hotSynchronizeLimit = 0)
    {
        $this->manager = $doctrine->getManager();

        // TODO: Quick fix, should be done using Dependency Injection, as it would prevent the RuntimeException!
        $repository = $this->manager->getRepository(AssetStorage::class);
        if (!$repository instanceof AssetStorageRepository) {
            throw new \RuntimeException(\sprintf('%s has a repository that should be of type %s. But %s is given.', EntityStorage::class, AssetStorage::class, $repository::class));
        }
        $this->repository = $repository;
    }

    #[\Override]
    public function head(string $hash): bool
    {
        return $this->repository->head($hash);
    }

    #[\Override]
    public function heads(string ...$hashes): array
    {
        return \array_values(\array_map(fn (string $hash) => $this->head($hash) ? true : $hash, $hashes));
    }

    #[\Override]
    public function getSize(string $hash): int
    {
        $size = $this->repository->getSize($hash);
        if (null === $size) {
            throw new NotFoundHttpException($hash);
        }

        return $size;
    }

    #[\Override]
    public function create(string $hash, string $filename): bool
    {
        $entity = $this->createEntity($hash);

        $content = \file_get_contents($filename);
        $size = \filesize($filename);

        if (false === $content || false === $size) {
            throw new FileNotFoundException($hash);
        }

        $entity->setSize($size);
        $entity->setContents($content);
        $entity->setConfirmed(true);
        $this->manager->persist($entity);
        $this->manager->flush();

        return true;
    }

    private function createEntity(string $hash): AssetStorage
    {
        $entity = $this->repository->findByHash($hash);
        if (null === $entity) {
            $entity = new AssetStorage();
            $entity->setHash($hash);
        }

        return $entity;
    }

    #[\Override]
    public function read(string $hash, bool $confirmed = true): StreamInterface
    {
        $entity = $this->repository->findByHash($hash, $confirmed);
        if (null === $entity) {
            throw new NotFoundHttpException($hash);
        }
        $contents = $entity->getContents();

        if (\is_resource($contents)) {
            return new Stream($contents);
        }
        $resource = \fopen('php://memory', 'w+');
        if (false === $resource) {
            throw new NotFoundHttpException($hash);
        }
        if (\is_string($contents)) {
            \fwrite($resource, $contents);
        }

        \rewind($resource);

        return new Stream($resource);
    }

    #[\Override]
    public function health(): bool
    {
        try {
            return $this->repository->count([]) >= 0;
        } catch (\Exception) {
        }

        return false;
    }

    #[\Override]
    public function __toString(): string
    {
        return EntityStorage::class;
    }

    #[\Override]
    public function remove(string $hash): bool
    {
        if (!$this->head($hash)) {
            return false;
        }

        return $this->repository->removeByHash($hash);
    }

    #[\Override]
    public function initUpload(string $hash, int $size, string $name, string $type): bool
    {
        $entity = $this->repository->findByHash($hash, false);
        if (null === $entity) {
            $entity = $this->createEntity($hash);
        }

        $entity->setSize(0);
        $entity->setContents('');
        $entity->setConfirmed(false);

        $this->manager->persist($entity);
        $this->manager->flush();

        return true;
    }

    #[\Override]
    public function finalizeUpload(string $hash): bool
    {
        $entity = $this->repository->findByHash($hash, false);
        if (null !== $entity) {
            $entity->setConfirmed(true);
            $entity->setSize(\strlen((string) $entity->getContents()));
            $this->manager->persist($entity);
            $this->manager->flush();

            return true;
        }

        return false;
    }

    #[\Override]
    public function addChunk(string $hash, string $chunk): bool
    {
        $entity = $this->repository->findByHash($hash, false);
        if (null !== $entity) {
            $contents = $entity->getContents();
            if (\is_resource($contents)) {
                $contents = \stream_get_contents($contents);
            }

            $entity->setContents($contents.$chunk);

            $entity->setSize($entity->getSize() + \strlen($chunk));
            $this->manager->persist($entity);
            $this->manager->flush();

            return true;
        }

        return false;
    }

    #[\Override]
    public function getUsage(): int
    {
        return $this->usage;
    }

    #[\Override]
    public function getHotSynchronizeLimit(): int
    {
        return $this->hotSynchronizeLimit;
    }

    #[\Override]
    public function removeUpload(string $hash): void
    {
        try {
            $entity = $this->repository->findByHash($hash, false);

            if (null !== $entity) {
                $this->repository->delete($entity);
            }
        } catch (\Throwable) {
        }
    }

    protected function isUsageSupported(int $usageRequested): bool
    {
        if ($usageRequested >= self::STORAGE_USAGE_EXTERNAL) {
            return false;
        }

        return $usageRequested <= $this->usage;
    }

    #[\Override]
    public function initFinalize(string $hash): void
    {
    }

    #[\Override]
    public function readCache(Config $config): ?StreamInterface
    {
        return null;
    }

    #[\Override]
    public function saveCache(Config $config, FileInterface $file): bool
    {
        return false;
    }

    #[\Override]
    public function clearCache(): bool
    {
        return false;
    }

    #[\Override]
    public function readFromArchiveInCache(string $hash, string $path): ?StreamWrapper
    {
        return null;
    }

    #[\Override]
    public function addFileInArchiveCache(string $hash, SplFileInfo $file, string $mimeType): bool
    {
        return false;
    }

    #[\Override]
    public function loadArchiveItemsInCache(string $archiveHash, Archive $archive, ?callable $callback = null): bool
    {
        return false;
    }
}
