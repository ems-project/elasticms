<?php

declare(strict_types=1);

namespace EMS\Helpers\Standard;

final class Type
{
    /**
     * @param mixed $value
     */
    public static function string($value): string
    {
        if (!\is_string($value)) {
            throw new \RuntimeException(\sprintf("Expect a string got '%s'", \gettype($value)));
        }

        return $value;
    }

    /**
     * @param mixed $value
     */
    public static function integer($value): int
    {
        if (!\is_int($value)) {
            throw new \RuntimeException(\sprintf("Expect an integer got '%s'", \gettype($value)));
        }

        return $value;
    }
}
