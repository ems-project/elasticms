<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Core\Component\JsonMenuNested\Config;

use function Symfony\Component\String\u;

class JsonMenuNestedColumn
{
    public function __construct(
        public readonly string $name,
        public readonly ?int $width = null,
    ) {
    }

    public function blockName(string $prefix): string
    {
        return u($this->name)->prepend($prefix.'_')->camel()->toString();
    }
}
