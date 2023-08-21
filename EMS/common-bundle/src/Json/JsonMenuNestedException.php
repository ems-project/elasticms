<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Json;

class JsonMenuNestedException extends \RuntimeException
{
    public static function itemNotFound(): self
    {
        return new self('json_menu_nested.error.not_found.item');
    }
}
