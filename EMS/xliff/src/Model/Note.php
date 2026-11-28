<?php

declare(strict_types=1);

namespace EMS\Xliff\Model;

final class Note
{
    public function __construct(
        public string $text,
        public ?string $from = null,
    ) {
    }
}
