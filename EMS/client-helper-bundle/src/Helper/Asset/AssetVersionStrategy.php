<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Asset;

use Symfony\Component\Asset\VersionStrategy\VersionStrategyInterface;

final class AssetVersionStrategy implements VersionStrategyInterface
{
    public function __construct(private readonly AssetHelperRuntime $assetHelperRuntime)
    {
    }

    /**
     * @param string $path
     */
    public function getVersion($path): string
    {
        return $this->assetHelperRuntime->getVersionHash();
    }

    /**
     * @param string $path
     */
    public function applyVersion($path): string
    {
        return $this->assetHelperRuntime->applyVersion($path);
    }
}
