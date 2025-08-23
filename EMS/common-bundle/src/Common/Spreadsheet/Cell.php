<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\Spreadsheet;

readonly class SpreadsheetCell
{
    /** @param array<mixed> $style */
    public function __construct(
        public string $data,
        public array $style = [],
        public ?string $type = null,
        public ?string $formatInput = null,
        public ?string $formatDisplay = null,
    ) {
    }

    public function isType(string $type): bool
    {
        return $this->type === $type;
    }

    public function hasStyle(): bool
    {
        return \count($this->style) > 0;
    }
}
