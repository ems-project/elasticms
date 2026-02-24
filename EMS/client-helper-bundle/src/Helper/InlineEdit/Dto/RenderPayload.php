<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\InlineEdit\Dto;

class RenderPayload
{
    /**
     * @param Editable[] $editables
     */
    public function __construct(
        public string $title,
        public array $editables,
    ) {
    }
}
