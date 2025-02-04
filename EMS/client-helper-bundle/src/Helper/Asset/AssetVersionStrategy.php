<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Asset;

use EMS\CommonBundle\Common\Vite\ViteDevServer;
use Symfony\Component\Asset\VersionStrategy\VersionStrategyInterface;

final readonly class AssetVersionStrategy implements VersionStrategyInterface
{
    public function __construct(
        private AssetHelperRuntime $assetHelperRuntime,
        private ViteDevServer $viteDevServer,
    ) {
    }

    /**
     * @param string $path
     */
    #[\Override]
    public function getVersion($path): string
    {
        return $this->assetHelperRuntime->getVersionHash();
    }

    /**
     * @param string $path
     */
    #[\Override]
    public function applyVersion($path): string
    {
        if ($this->viteDevServer->isRunning() && !\str_ends_with($path, '.css')) {
            return $this->viteDevServer->getPath($path);
        }

        return $this->assetHelperRuntime->applyVersion($path);
    }
}
