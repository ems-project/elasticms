<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Asset;

use Symfony\Component\Asset\VersionStrategy\VersionStrategyInterface;

final readonly class AssetVersionStrategy implements VersionStrategyInterface
{
    public function __construct(private AssetHelperRuntime $assetHelperRuntime)
    {
    }

    #[\Override]
    public function getVersion(string $path): string
    {
        return $this->assetHelperRuntime->getVersionHash();
    }

    #[\Override]
    public function applyVersion(string $path): string
    {
        return $this->assetHelperRuntime->applyVersion($path);
    }
}
