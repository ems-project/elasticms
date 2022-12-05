<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Storage\Service;

use Psr\Log\LoggerInterface;

class FileSystemStorage extends AbstractUrlStorage
{
    public function __construct(LoggerInterface $logger, private readonly string $storagePath, int $usage, int $hotSynchronizeLimit = 0)
    {
        parent::__construct($logger, $usage, $hotSynchronizeLimit);
    }

    protected function getBaseUrl(): string
    {
        return $this->storagePath;
    }

    public function __toString(): string
    {
        return FileSystemStorage::class." ($this->storagePath)";
    }

    /**
     * @return null
     */
    protected function getContext()
    {
        return null;
    }
}
