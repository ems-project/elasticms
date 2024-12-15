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

    public function head(string $hash): bool
    {
        return $this->repository->head($hash);
    }

    public function heads(string ...$hashes): array
    {
        return \array_values(\array_map(fn (string $hash) => $this->head($hash) ? true : $hash, $hashes));
    }

    public function getSize(string $hash): int
    {
        $size = $this->repository->getSize($hash);
        if (null === $size) {
            throw new NotFoundHttpException($hash);
        }

        return $size;
    }

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

    public function health(): bool
    {
        try {
            return $this->repository->count([]) >= 0;
        } catch (\Exception) {
        }

        return false;
    }

    public function __toString(): string
    {
        return EntityStorage::class;
    }

    public function remove(string $hash): bool
    {
        if (!$this->head($hash)) {
            return false;
        }

        return $this->repository->removeByHash($hash);
    }

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

    public function getUsage(): int
    {
        return $this->usage;
    }

    public function getHotSynchronizeLimit(): int
    {
        return $this->hotSynchronizeLimit;
    }

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

    public function initFinalize(string $hash): void
    {
    }

    public function readCache(Config $config): ?StreamInterface
    {
        return null;
    }

    public function saveCache(Config $config, FileInterface $file): bool
    {
        return false;
    }

    public function clearCache(): bool
    {
        return false;
    }

    public function readFromArchiveInCache(string $hash, string $path): ?StreamWrapper
    {
        return null;
    }

    public function addFileInArchiveCache(string $hash, SplFileInfo $file, string $mimeType): bool
    {
        return false;
    }

    public function loadArchiveItemsInCache(string $archiveHash, Archive $archive, ?callable $callback = null): bool
    {
        return false;
    }
}
