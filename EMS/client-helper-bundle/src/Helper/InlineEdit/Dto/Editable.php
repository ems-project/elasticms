<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\InlineEdit\Dto;

class Editable
{
    /**
     * @param EditableElement[] $elements
     */
    public function __construct(
        public string $emsId,
        public array $elements,
    ) {
    }
}
