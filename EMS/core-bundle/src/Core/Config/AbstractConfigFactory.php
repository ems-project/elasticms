<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Core\Config;

use EMS\CommonBundle\Storage\NotFoundException;
use EMS\CommonBundle\Storage\StorageManager;
use EMS\Helpers\Standard\Json;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

abstract class AbstractConfigFactory implements ConfigFactoryInterface
{
    private ?StorageManager $storageManager = null;

    public function getStorageManager(): StorageManager
    {
        return $this->storageManager ?: throw new \Exception('Storage manager not set');
    }

    public function setStorageManager(StorageManager $storageManager): void
    {
        $this->storageManager = $storageManager;
    }

    /**
     * @param array<mixed> $options
     */
    protected function getHash(array $options): string
    {
        return $this->getStorageManager()->saveConfig($options);
    }

    /**
     * @return array<mixed>
     */
    protected function getOptions(string $hash): array
    {
        try {
            return Json::decode($this->getStorageManager()->getContents($hash));
        } catch (NotFoundException) {
            throw new NotFoundHttpException();
        }
    }
}
