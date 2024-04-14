<?php

namespace EMS\CoreBundle\Form\DataTransformer;

use EMS\CommonBundle\Routes;
use EMS\CommonBundle\Storage\StorageManager;
use EMS\CoreBundle\Entity\Form\AssetEntity;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouterInterface;

/**
 * @implements DataTransformerInterface<string, AssetEntity>
 */
readonly class AssetTransformer implements DataTransformerInterface
{
    public function __construct(
        private RouterInterface $router,
        private StorageManager $storageManager,
    ) {
    }

    public function transform(mixed $value): AssetEntity
    {
        if (!\is_string($value)) {
            throw new \RuntimeException('Unexpected non string image value');
        }
        $asset = new AssetEntity();
        if ('' === $value) {
            return $asset;
        }

        $context = new RequestContext();
        $this->router->setContext($context);
        $match = $this->router->match($value);
        if (Routes::ASSET !== $match['_route']) {
            throw new \RuntimeException('Was expecting an asset route');
        }
        $config = $this->storageManager->getConfig($match['hash_config']);
        $asset->setFilename($match['filename']);
        $asset->setHash($match['hash']);
        $asset->setConfig($config);

        return $asset;
    }

    public function reverseTransform(mixed $value): string
    {
        if (!$value instanceof AssetEntity) {
            throw new \RuntimeException('Unexpected non AssetEntity object');
        }

        return '';
    }
}
