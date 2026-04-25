<?php

declare(strict_types=1);

namespace EMS\Xliff;

use EMS\Xliff\Html\Policy\TranslatableAttributes;

final readonly class Options
{
    public function __construct(
        public string $defaultVersion = Version::V12,
        public bool $preserveWhitespace = false,
        public bool $formatOutput = true,
        public ?TranslatableAttributes $translatableAttributes = null,
    ) {
    }
}
