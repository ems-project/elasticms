<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Json;

class JsonMenuNestedException extends \RuntimeException
{
    public static function itemNotFound(): self
    {
        return new self('Item not found');
    }

    public static function itemParentNotFound(): self
    {
        return new self('Parent not found');
    }

    public static function moveChildMissing(): self
    {
        return new self('Move failed, current parent does not have item');
    }

    public static function moveChildExists(): self
    {
        return new self('Move failed, new parent already has item');
    }
}
