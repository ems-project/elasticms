<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Storage;

class NotFoundException extends \RuntimeException
{
    public function __construct(string $hash)
    {
        parent::__construct(\sprintf('Asset identified by the hash %s not found', $hash));
    }
}
