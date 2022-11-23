<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Storage;

class NotSavedException extends \RuntimeException
{
    private string $hash;

    public function __construct(string $hash)
    {
        parent::__construct(\sprintf('Asset identified by the hash %s not saved', $hash));
        $this->hash = $hash;
    }

    public function getHash(): string
    {
        return $this->hash;
    }
}
