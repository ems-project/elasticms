<?php

declare(strict_types=1);

namespace EMS\Helpers\Standard;

final class Json
{
    public static function encode(mixed $value, bool $pretty = false): string
    {
        $options = $pretty ? (JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : 0;
        $encoded = \json_encode($value, $options);

        if (false === $encoded) {
            throw new \RuntimeException('failed encoding json');
        }

        return $encoded;
    }

    public static function escape(string $value, bool $pretty = false): string
    {
        $encoded = self::encode($value, $pretty);
        if (\strlen($encoded) < 2) {
            throw new \RuntimeException('Unexpected too short string');
        }

        return \substr($encoded, 1, \strlen($encoded) - 2);
    }

    /**
     * @return array<mixed>
     */
    public static function decode(string $value): array
    {
        $decoded = \json_decode($value, true);

        if (JSON_ERROR_NONE !== \json_last_error() || !\is_array($decoded)) {
            throw new \RuntimeException(\sprintf('Invalid json %s', \json_last_error_msg()));
        }

        return $decoded;
    }

    public static function isJson(string $string): bool
    {
        \json_decode($string);

        return JSON_ERROR_NONE === \json_last_error();
    }
}
