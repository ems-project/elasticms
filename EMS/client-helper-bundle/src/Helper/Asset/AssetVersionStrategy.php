<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Asset;

use Symfony\Component\Asset\VersionStrategy\VersionStrategyInterface;

final class AssetVersionStrategy implements VersionStrategyInterface
{
    public function __construct(private readonly AssetHelperRuntime $assetHelperRuntime, private readonly ?string $localFolder)
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
        if (!empty($this->localFolder)) {
            return \sprintf('%s/%s?hash=%s', $this->localFolder, $path, $this->assetHelperRuntime->getVersionHash());
        }

        return \sprintf('%s/%s/%s', $this->assetHelperRuntime->getVersionSaveDir(), $this->assetHelperRuntime->getVersionHash(), $path);
    }
}
