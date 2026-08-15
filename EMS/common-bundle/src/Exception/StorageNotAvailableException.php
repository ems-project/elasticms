<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Exception;

use EMS\CommonBundle\Storage\Service\StorageInterface;

class StorageNotAvailableException extends \Exception
{
    public function __construct(StorageInterface $adapter)
    {
        parent::__construct(\sprintf('The storage service %s is not available', $adapter->__toString()));
    }
}