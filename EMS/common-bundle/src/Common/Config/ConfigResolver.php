<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\Config;

use EMS\CommonBundle\Common\Admin\AdminHelper;
use EMS\CommonBundle\Storage\File\FileInterface;
use EMS\CommonBundle\Storage\NotFoundException;
use EMS\CommonBundle\Storage\StorageManager;
use EMS\Helpers\Standard\Json;

final readonly class ConfigResolver
{
    public function __construct(
        private StorageManager $storageManager,
        private AdminHelper $adminHelper,
    ) {
    }

    public function resolve(string $input): string
    {
        return match (true) {
            Json::isJson($input) => $input,
            $this->getFile($input) instanceof FileInterface => $this->getFile($input)->getContent(),
        };
    }

    private function getFile(string $fileIdentifier): FileInterface
    {
        try {
            return $this->storageManager->getFile($fileIdentifier);
        } catch (NotFoundException) {
            return $this->adminHelper->getCoreApi()->file()->getFile($fileIdentifier);
        }
    }
}
