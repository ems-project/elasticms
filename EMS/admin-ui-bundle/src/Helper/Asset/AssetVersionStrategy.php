<?php

declare(strict_types=1);

namespace EMS\AdminUIBundle\Helper\Asset;

use EMS\CommonBundle\Common\Asset\ViteService;
use Symfony\Component\Asset\VersionStrategy\VersionStrategyInterface;
use Symfony\Component\HttpKernel\Config\FileLocator;

final readonly class AssetVersionStrategy implements VersionStrategyInterface
{
    public function __construct(
        private FileLocator $fileLocator,
        private ViteService $viteService,
        private string $basePath = 'bundles/emsadminui/',
    ) {
    }

    #[\Override]
    public function getVersion(string $path): string
    {
        return $this->applyVersion($path);
    }

    #[\Override]
    public function applyVersion(string $path): string
    {
        $this->viteService->loadManifestFromDirectory(
            directory: $this->fileLocator->locate('@EMSAdminUIBundle/public')
        );

        $devPath = $this->viteService->devPath($path);

        return $devPath ?? $this->basePath.$this->viteService->path($path);
    }
}
