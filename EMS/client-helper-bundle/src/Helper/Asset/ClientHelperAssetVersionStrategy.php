<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Asset;

use EMS\CommonBundle\Common\Asset\ViteService;
use Symfony\Component\Asset\VersionStrategy\VersionStrategyInterface;
use Symfony\Component\HttpKernel\Config\FileLocator;

readonly class ClientHelperAssetVersionStrategy implements VersionStrategyInterface
{
    public function __construct(
        private FileLocator $fileLocator,
        private ViteService $viteService,
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
            directory: $this->fileLocator->locate('@EMSClientHelperBundle/public')
        );

        $devPath = $this->viteService->devPath($path);

        return $devPath ?? 'bundles/emsclienthelper/'.$this->viteService->path($path);
    }
}
