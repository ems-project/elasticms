<?php

declare(strict_types=1);

namespace EMS\AdminUIBundle\Helper\Asset;

use EMS\CommonBundle\Common\Vite\ViteDevServer;
use EMS\CommonBundle\Common\Vite\ViteManifest;
use Symfony\Component\Asset\VersionStrategy\VersionStrategyInterface;
use Symfony\Component\HttpKernel\Config\FileLocator;

final class AssetVersionStrategy implements VersionStrategyInterface
{
    public function __construct(
        private readonly FileLocator $fileLocator,
        private readonly ViteDevServer $viteDevServer,
        private readonly string $basePath = 'bundles/emsadminui/',
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
        return $this->getManifestPath($path) ?: $path;
    }

    private function getManifestPath(string $path): string
    {
        if ($this->viteDevServer->isRunning() && !\str_ends_with($path, '.css')) {
            return $this->viteDevServer->getPath($path);
        }

        $viteManifest = ViteManifest::fromDirectory($this->fileLocator->locate('@EMSAdminUIBundle/public'));
        $path = $viteManifest?->matchPath($path) ?? $path;

        return $this->basePath.$path;
    }
}
