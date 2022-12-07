<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Core\Config;

use EMS\CommonBundle\Storage\NotFoundException;
use EMS\CommonBundle\Storage\StorageManager;
use EMS\Helpers\Standard\Json;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

abstract class AbstractConfigFactory implements ConfigFactoryInterface
{
    public function __construct(
        private readonly StorageManager $storageManager
    ) {
    }

    /**
     * @param array<mixed> $options
     */
    protected function getHash(array $options): string
    {
        return $this->storageManager->saveConfig($options);
    }

    /**
     * @return array<mixed>
     */
    protected function getOptions(string $hash): array
    {
        try {
            return Json::decode($this->storageManager->getContents($hash));
        } catch (NotFoundException) {
            throw new NotFoundHttpException();
        }
    }
}
