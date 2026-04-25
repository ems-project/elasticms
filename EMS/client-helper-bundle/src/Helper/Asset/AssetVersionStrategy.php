<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Asset;

use EMS\ClientHelperBundle\Twig\AssetExtension;
use Symfony\Component\Asset\VersionStrategy\VersionStrategyInterface;

final readonly class AssetVersionStrategy implements VersionStrategyInterface
{
    public function __construct(private AssetExtension $assetExtension)
    {
    }

    #[\Override]
    public function getVersion(string $path): string
    {
        return $this->assetExtension->getVersionHash();
    }

    #[\Override]
    public function applyVersion(string $path): string
    {
        return $this->assetExtension->applyVersion($path);
    }
}
