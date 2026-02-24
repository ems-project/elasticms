<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\InlineEdit\Dto;

class EditableElement
{
    public function __construct(
        public string $tag,
        public string $path,
    ) {
    }
}
