<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Asset;

use EMS\ClientHelperBundle\Helper\Elasticsearch\ClientRequestManager;
use EMS\CommonBundle\Storage\StorageManager;
use EMS\CommonBundle\Twig\AssetRuntime;
use Symfony\Component\Filesystem\Filesystem;
use Twig\Extension\RuntimeExtensionInterface;

final class AssetHelperRuntime implements RuntimeExtensionInterface
{
    private StorageManager $storageManager;
    private ClientRequestManager $manager;
    private string $publicDir;
    private Filesystem $filesystem;
    private ?string $versionHash = null;
    private ?string $versionSaveDir = null;

    public function __construct(StorageManager $storageManager, ClientRequestManager $manager, string $projectDir)
    {
        $this->storageManager = $storageManager;
        $this->manager = $manager;
        $this->publicDir = $projectDir.'/public';

        $this->filesystem = new Filesystem();
    }

    public function setVersion(string $hash, string $saveDir = 'bundles'): string
    {
        if (null !== $this->versionHash && $this->versionHash !== $hash) {
            throw new \RuntimeException('Another hash version has been already defined');
        }
        $this->versionHash = $hash;
        $this->versionSaveDir = $saveDir;

        return $this->assets($hash, $saveDir, false);
    }

    public function assets(string $hash, string $saveDir = 'bundles', bool $addEnvironmentSymlink = true): string
    {
        $basePath = $this->publicDir.\DIRECTORY_SEPARATOR.$saveDir.\DIRECTORY_SEPARATOR;
        $directory = $basePath.$hash;

        try {
            if (!$this->filesystem->exists($directory)) {
                AssetRuntime::extract($this->storageManager->getStream($hash), $directory);
                $this->filesystem->touch($directory.\DIRECTORY_SEPARATOR.$hash);
            }
            if (!$addEnvironmentSymlink) {
                return $directory;
            }

            $cacheKey = $this->manager->getDefault()->getAlias();
            $symlink = $basePath.$cacheKey;
            if ($this->filesystem->exists($symlink.\DIRECTORY_SEPARATOR.$hash)) {
                return $directory;
            }

            $this->manager->getLogger()->warning('switching assets {symlink} to {hash}', ['symlink' => $symlink, 'hash' => $hash]);
            $this->filesystem->remove($symlink);
            $this->filesystem->symlink($directory, $symlink, true);
        } catch (\Exception $e) {
            $this->manager->getLogger()->error('emsch_assets failed : {error}', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        }

        return $directory;
    }

    public function getVersionHash(): string
    {
        if (null === $this->versionHash) {
            throw new \RuntimeException('Asset version has not been set');
        }

        return $this->versionHash;
    }

    public function getVersionSaveDir(): string
    {
        if (null === $this->versionSaveDir) {
            throw new \RuntimeException('Asset version has not been set');
        }

        return $this->versionSaveDir;
    }
}
