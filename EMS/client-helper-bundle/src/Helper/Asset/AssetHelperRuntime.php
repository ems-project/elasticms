<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Asset;

use EMS\ClientHelperBundle\Helper\Elasticsearch\ClientRequestManager;
use EMS\CommonBundle\Helper\EmsFields;
use EMS\CommonBundle\Storage\StorageManager;
use EMS\CommonBundle\Twig\AssetRuntime;
use Symfony\Component\Filesystem\Filesystem;
use Twig\Extension\RuntimeExtensionInterface;

final class AssetHelperRuntime implements RuntimeExtensionInterface
{
    private readonly string $publicDir;
    private readonly Filesystem $filesystem;
    private ?string $versionHash = null;
    private ?string $versionSaveDir = null;

    public function __construct(private readonly StorageManager $storageManager, private readonly ClientRequestManager $manager, private readonly AssetRuntime $commonAssetRuntime, string $projectDir, private readonly ?string $localFolder)
    {
        $this->publicDir = $projectDir.'/public';

        $this->filesystem = new Filesystem();
    }

    public function setVersion(string $hash, ?string $saveDir = 'bundles'): ?string
    {
        if (null !== $this->versionHash && $this->versionHash !== $hash) {
            throw new \RuntimeException('Another hash version has been already defined');
        }
        $this->versionHash = $hash;
        if (null === $saveDir) {
            return null;
        }
        \trigger_error('Specify a save directory and retrieving a path to the assets are deprecated, use emsch_assets_version with a null saveDir parameter', E_USER_DEPRECATED);
        $this->versionSaveDir = $saveDir;
        if (!empty($this->localFolder)) {
            return $this->publicDir.DIRECTORY_SEPARATOR.$this->localFolder;
        }

        return $this->assets($hash, $saveDir, false);
    }

    public function assets(string $hash, string $saveDir = 'bundles', bool $addEnvironmentSymlink = true): string
    {
        \trigger_error('The function emsch_assets id deprecated, use emsch_assets_version with a null saveDir parameter instead', E_USER_DEPRECATED);
        $basePath = $this->publicDir.\DIRECTORY_SEPARATOR.$saveDir.\DIRECTORY_SEPARATOR;
        $directory = $basePath.$hash;

        try {
            if (!$this->filesystem->exists($directory.\DIRECTORY_SEPARATOR.$hash)) {
                $tempDir = $this->storageManager->extractFromArchive($hash);
                $tempDir->touch($hash);
                $tempDir->moveTo($directory);
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

    /**
     * @param array<string, mixed> $assetConfig
     */
    public function asset(string $path, array $assetConfig = []): string
    {
        if (empty($this->localFolder)) {
            $filename = \sprintf('%s:%s', $this->getVersionHash(), $path);
        } else {
            $filename = $this->publicDir.DIRECTORY_SEPARATOR.$this->localFolder.DIRECTORY_SEPARATOR.$path;
        }
        $basename = \basename($path);

        return $this->commonAssetRuntime->assetPath([
            EmsFields::CONTENT_FILE_NAME_FIELD => $basename,
        ], \array_merge([
            EmsFields::ASSET_CONFIG_FILE_NAMES => [$filename],
        ], $assetConfig));
    }

    public function applyVersion(string $path): string
    {
        if (!empty($this->localFolder)) {
            return \sprintf('%s/%s', $this->localFolder, $path);
        }
        if (null === $this->versionSaveDir) {
            return \sprintf('bundles/%s/%s', $this->getVersionHash(), $path);
        }

        return \sprintf('%s/%s/%s', $this->getVersionSaveDir(), $this->getVersionHash(), $path);
    }

    public function getVersionHash(): string
    {
        if (null === $this->versionHash) {
            throw new \RuntimeException('Asset version has not been set');
        }

        return $this->versionHash;
    }

    public function getVersionSaveDir(): ?string
    {
        if (null === $this->versionSaveDir) {
            throw new \RuntimeException('Asset version has not been set');
        }

        return $this->versionSaveDir;
    }
}
