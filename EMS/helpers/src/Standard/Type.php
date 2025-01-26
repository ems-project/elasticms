<?php

declare(strict_types=1);

namespace EMS\Helpers\Standard;

final class Type
{
    public static function bool(mixed $value): bool
    {
        if (!\is_bool($value)) {
            throw new \RuntimeException(\sprintf("Expect a bool got '%s'", \gettype($value)));
        }

        return $value;
    }

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

    /**
     * @return mixed[]
     */
    public static function array(mixed $value): array
    {
        if (!\is_array($value)) {
            throw new \RuntimeException(\sprintf("Expect an array got '%s'", \gettype($value)));
        }

        return $value;
    }

    public static function gdImage(mixed $value): \GdImage
    {
        if (!$value instanceof \GdImage) {
            throw new \RuntimeException(\sprintf("Expect a \GdImage got '%s'", \gettype($value)));
        }

        return $value;
    }

    public static function getAsNullableString(mixed $value): ?string
    {
        if (null === $value) {
            return null;
        }
        if (!\is_scalar($value)) {
            throw new \RuntimeException(\sprintf("Expect a scalar variable got '%s'", \gettype($value)));
        }

        return (string) $value;
    }
}
