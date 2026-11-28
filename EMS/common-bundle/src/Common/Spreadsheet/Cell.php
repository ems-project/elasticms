<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\Spreadsheet;

readonly class Cell
{
    public const string CELL_DATA = 'data';
    public const string CELL_FORMAT_DISPLAY = 'format_display';
    public const string CELL_FORMAT_INPUT = 'format_input';
    public const string CELL_STYLE = 'style';
    public const string CELL_TYPE = 'type';
    public const string TYPE_DATE = 'date';

    /** @param array<mixed> $style */
    public function __construct(
        public mixed $data,
        public array $style = [],
        public ?string $type = null,
        public ?string $formatInput = null,
        public ?string $formatDisplay = null,
    ) {
    }

    public function hasStyle(): bool
    {
        return [] !== $this->style;
    }

    public function isType(string $type): bool
    {
        return $this->type === $type;
    }
}
