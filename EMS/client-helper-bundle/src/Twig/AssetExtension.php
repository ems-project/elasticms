<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Twig;

use EMS\CommonBundle\Common\Asset\ViteService;
use EMS\CommonBundle\Controller\FileController;
use EMS\CommonBundle\Helper\EmsFields;
use EMS\CommonBundle\Storage\StorageManager;
use EMS\CommonBundle\Twig\AssetExtension as CommonAssetExtension;
use Symfony\Component\Filesystem\Filesystem;
use Twig\Attribute\AsTwigFunction;

final class AssetExtension
{
    private readonly string $publicDir;
    private ?string $versionHash = null;
    private ?string $localFolder = null;
    private string $publishPath = 'bundles';

    public function __construct(
        private readonly StorageManager $storageManager,
        private readonly CommonAssetExtension $commonAssetExtension,
        private readonly ViteService $viteService,
        string $projectDir,
        ?string $localFolder = null
    ) {
        $this->publicDir = $projectDir.'/public';
        if (!\is_string($localFolder) || '' === $localFolder) {
            return;
        }
        $filesystem = new Filesystem();
        $folder = $this->publicDir.'/'.$localFolder;
        if (!\str_starts_with($localFolder, '../') || !$filesystem->exists($folder)) {
            $this->localFolder = $localFolder;

            return;
        }

        $symlink = $this->publicDir.'/bundles/emssymlink';
        if (\is_link($symlink)) {
            $target = \readlink($symlink);
            if ($target === $folder) {
                $this->localFolder = 'bundles/emssymlink';

                return;
            }
            $filesystem->remove($symlink);
        }

        if ($filesystem->exists($symlink)) {
            throw new \RuntimeException('The /bundles/emssymlink already exists.');
        }
        $filesystem->symlink($folder, $symlink);
        $this->localFolder = 'bundles/emssymlink';
    }

    public function applyVersion(string $path): string
    {
        $basePath = $this->getBasePath();

        if (null === $this->localFolder) {
            $manifestId = $this->viteService->loadManifestFromEmsArchive($this->getVersionHash());
        } else {
            $manifestId = $this->viteService->loadManifestFromDirectory($basePath);
        }

        $devPath = $this->viteService->devPath($path);

        return $devPath ?? $basePath.\DIRECTORY_SEPARATOR.$this->viteService->path($path, $manifestId);
    }

    /**
     * @param array<string, mixed> $assetConfig
     */
    #[AsTwigFunction(name: 'emsch_asset', isSafe: ['html'])]
    public function asset(string $path, array $assetConfig = []): string
    {
        $filename = $this->getAssetFilename($path);
        $basename = \basename($path);

        return $this->commonAssetExtension->assetPath([
            EmsFields::CONTENT_FILE_NAME_FIELD => $basename,
        ], \array_merge([
            EmsFields::ASSET_CONFIG_FILE_NAMES => [$filename],
        ], $assetConfig));
    }

    /**
     * @param array<string, mixed> $assetConfig
     *
     * @return array{controller: string, path: array{hash_config: string, hash: string, filename: string }}
     */
    #[AsTwigFunction(name: 'emsch_asset_redirect')]
    public function assetRedirect(string $path, array $assetConfig = []): array
    {
        $filename = $this->getAssetFilename($path);
        $basename = \basename($path);
        $hashConfig = $this->storageManager->saveConfig(\array_merge([
            EmsFields::ASSET_CONFIG_FILE_NAMES => [$filename],
        ], $assetConfig));

        return [
            'controller' => \sprintf('%s::asset', FileController::class),
            'path' => [
                'hash_config' => $hashConfig,
                'hash' => 'processor',
                'filename' => $basename,
            ],
        ];
    }

    public function getVersionHash(): string
    {
        if (null === $this->versionHash) {
            throw new \RuntimeException('Asset version has not been set');
        }

        return $this->versionHash;
    }

    #[AsTwigFunction(name: 'emsch_assets_version')]
    public function setVersion(string $hash, ?string $publishPath = null): void
    {
        if (null !== $this->versionHash && $this->versionHash !== $hash) {
            throw new \RuntimeException('Another hash version has been already defined');
        }
        $this->versionHash = $hash;
        $this->publishPath = $publishPath ?? $this->publishPath;
    }

    private function getAssetFilename(string $path): string
    {
        if (null !== $this->localFolder) {
            return $this->publicDir.DIRECTORY_SEPARATOR.$this->localFolder.DIRECTORY_SEPARATOR.$path;
        }

        return \sprintf('%s:%s', $this->getVersionHash(), $path);
    }

    private function getBasePath(): string
    {
        return match (true) {
            !empty($this->localFolder) => $this->localFolder,
            default => \sprintf('%s/%s', $this->publishPath, $this->getVersionHash()),
        };
    }
}
