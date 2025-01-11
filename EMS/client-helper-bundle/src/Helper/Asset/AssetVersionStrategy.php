<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Asset;

use Symfony\Component\Asset\VersionStrategy\VersionStrategyInterface;

final readonly class AssetVersionStrategy implements VersionStrategyInterface
{
    public function __construct(private AssetHelperRuntime $assetHelperRuntime)
    {
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
        return $this->assetHelperRuntime->applyVersion($path);
    }
}
