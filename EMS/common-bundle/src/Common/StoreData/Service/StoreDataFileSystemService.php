<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\StoreData\Service;

use EMS\CommonBundle\Common\StoreData\StoreDataHelper;
use EMS\Helpers\File\File;
use EMS\Helpers\File\Folder;
use EMS\Helpers\Standard\Json;

class StoreDataFileSystemService implements StoreDataServiceInterface
{
    public function __construct(private readonly string $rootPath)
    {
    }

    #[\Override]
    public function save(StoreDataHelper $data): void
    {
        $filename = $this->filename($data->getKey());
        File::putContents($filename, Json::encode($data->getData(), true));
    }

    #[\Override]
    public function read(string $key): ?StoreDataHelper
    {
        $filename = $this->filename($key);
        if (!\file_exists($filename)) {
            return null;
        }
        if (false === $json = \file_get_contents($filename)) {
            throw new \RuntimeException(\sprintf('Error while retrieving data from %s', $filename));
        }

        return new StoreDataHelper($key, Json::decode($json));
    }

    #[\Override]
    public function delete(string $key): void
    {
        $filename = $this->filename($key);
        if (!\file_exists($filename)) {
            return;
        }
        if (false === \unlink($filename)) {
            throw new \RuntimeException(\sprintf('Error while deleting data from %s', $filename));
        }
    }

    private function filename(string $key): string
    {
        $invalidCharacterCounter = \preg_match('/[\^\*\?"<>|:]/', $key);
        if (\is_int($invalidCharacterCounter) && $invalidCharacterCounter > 0) {
            throw new \RuntimeException(\sprintf('The key %s contains %d invalid character(s): ^, *, ?, <, >, | ou :', $key, $invalidCharacterCounter));
        }
        $realPath = Folder::getRealPath($this->rootPath);

        return Folder::createFileDirectories(\sprintf('%s%s%s.json', $realPath, DIRECTORY_SEPARATOR, $key));
    }

    #[\Override]
    public function gc(): void
    {
    }
}
