<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Core\Component\JsonMenuNested;

use EMS\CoreBundle\Core\Config\ConfigInterface;

class JsonMenuNestedConfig implements ConfigInterface
{
    public function __construct(
        private readonly string $hash,
        private readonly string $id
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getHash(): string
    {
        return $this->hash;
    }
}
