<?php

declare(strict_types=1);

namespace EMS\AdminUIBundle\Helper\Asset;

use EMS\Helpers\Standard\Json;
use EMS\Helpers\Standard\Type;
use Symfony\Component\Asset\Exception\RuntimeException;
use Symfony\Component\Asset\VersionStrategy\VersionStrategyInterface;
use Symfony\Component\HttpKernel\Config\FileLocator;

final class AssetVersionStrategy implements VersionStrategyInterface
{
    /**
     * @var array<string, array{string: type}>
     */
    private array $manifestData;

    public function __construct(private readonly FileLocator $fileLocator, private readonly string $basePath = 'bundles/emsadminui/')
    {
    }

    public function getVersion(string $path): string
    {
        return $this->applyVersion($path);
    }

    public function applyVersion(string $path): string
    {
        return $this->getManifestPath($path) ?: $path;
    }

    private function getManifestPath(string $path): string
    {
        if (!isset($this->manifestData)) {
            $manifestPath = $this->fileLocator->locate('@EMSAdminUIBundle/Resources/public/.vite/manifest.json');
            if (!\is_file($manifestPath)) {
                throw new RuntimeException(\sprintf('Asset manifest file "%s" does not exist. Did you forget to build the assets with npm or yarn?', $manifestPath));
            }
            $this->manifestData = Json::decode(Type::string(\file_get_contents($manifestPath)));
        }

        if (\preg_match('/(?<path>.*\.(js|ts|cjs))(\.(?<index>[0-9]+))?\.css$/', $path, $matches) > 0 && isset($this->manifestData[$matches['path']]['css'][$matches['index'] ?? 0])) {
            return $this->basePath.$this->manifestData[$matches['path']]['css'][$matches['index'] ?? 0];
        }

        if (isset($this->manifestData[$path]['file'])) {
            return $this->basePath.$this->manifestData[$path]['file'];
        }

        return $this->basePath.$path;
    }
}
