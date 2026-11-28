<?php

declare(strict_types=1);

namespace EMS\Xliff\Model\Inline;

class Placeholder extends Node
{
    public function __construct(
        public readonly string $id,
        public readonly string $type,
        public readonly ?string $equivalentText,
    ) {
    }
}
