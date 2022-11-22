<?php

declare(strict_types=1);

namespace EMS\Helpers\Standard;

final class Base64
{
    public static function encode(string $value): string
    {
        return \base64_encode($value);
    }

    public static function decode(string $value): string
    {
        $decoded = \base64_decode($value, true);

        if (false === $decoded) {
            throw new \RuntimeException(\sprintf('Invalid base64 %s', $value));
        }

        return $decoded;
    }
}
