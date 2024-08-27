<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\Standard;

final class Type
{
    public static function string(mixed $value): string
    {
        if (!\is_string($value)) {
            throw new \RuntimeException(\sprintf("Expect a string got '%s'", \gettype($value)));
        }

        return $value;
    }

    public static function integer(mixed $value): int
    {
        if (!\is_int($value)) {
            throw new \RuntimeException(\sprintf("Expect an integer got '%s'", \gettype($value)));
        }

        return $value;
    }

    public static function bool(mixed $value): bool
    {
        if (!\is_bool($value)) {
            throw new \RuntimeException(\sprintf("Expect an boolean got '%s'", \gettype($value)));
        }

        return $value;
    }
}
