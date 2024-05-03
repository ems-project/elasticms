<?php

declare(strict_types=1);

namespace EMS\Helpers\Standard;

final class Hash
{
    public static function string(string $value, ?string $prefix = null): string
    {
        return self::hash($value, $prefix);
    }

    /**
     * @param array<mixed> $value
     */
    public static function array(array $value, ?string $prefix = null): string
    {
        return self::hash(Json::encode($value), $prefix);
    }

    private static function hash(string $value, ?string $prefix = null): string
    {
        return $prefix.\sha1($value);
    }
}
