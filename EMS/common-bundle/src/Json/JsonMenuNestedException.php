<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Json;

class JsonMenuNestedException extends \RuntimeException
{
    public static function itemNotFound(): self
    {
        return new self('json_menu_nested.error.item_not_found');
    }

    public static function itemParentNotFound(): self
    {
        return new self('json_menu_nested.error.item_parent_not_found');
    }

    public static function moveChildMissing(): self
    {
        return new self('json_menu_nested.error.move_child_missing');
    }

    public static function moveChildExists(): self
    {
        return new self('json_menu_nested.error.move_child_exists');
    }
}
