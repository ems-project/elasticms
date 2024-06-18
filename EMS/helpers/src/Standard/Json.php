<?php

declare(strict_types=1);

namespace EMS\Helpers\Standard;

final class Json
{
    public static function encode(mixed $value, bool $pretty = false, bool $unescapeUnicode = false): string
    {
        $options = $pretty ? (JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : 0;
        $options |= $unescapeUnicode ? JSON_UNESCAPED_UNICODE : 0;
        $encoded = \json_encode($value, $options | JSON_INVALID_UTF8_IGNORE);

        if (false === $encoded) {
            throw new \RuntimeException(\sprintf('failed encoding json: %s', \json_last_error_msg()));
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
    public static function decode(string $value, ?string $invalidMessage = null): array
    {
        $decoded = \json_decode($value, true);

        if (JSON_ERROR_NONE !== \json_last_error() || !\is_array($decoded)) {
            throw new \RuntimeException($invalidMessage ?? \sprintf('Invalid json %s', \json_last_error_msg()));
        }

        return $decoded;
    }

    public static function mixedDecode(string $value): mixed
    {
        $decoded = \json_decode($value, true);

        if (JSON_ERROR_NONE !== \json_last_error()) {
            throw new \RuntimeException(\sprintf('Invalid json %s', \json_last_error_msg()));
        }

        return $decoded;
    }

    public static function isJson(string $string): bool
    {
        \json_decode($string);

        return JSON_ERROR_NONE === \json_last_error();
    }

    /**
     * @return array<mixed>
     */
    public static function decodeFile(string $path): array
    {
        if (!\file_exists($path)) {
            throw new \RuntimeException(\sprintf('File does not exists: %s', $path));
        }

        $content = \file_get_contents($path, true);

        if (!\is_string($content)) {
            throw new \RuntimeException(\sprintf('No content for %s', $path));
        }

        return self::decode($content);
    }

    public static function prettyPrint(string $data): string
    {
        try {
            $formatted = \json_decode($data, true, 512, JSON_THROW_ON_ERROR);

            return self::encode($formatted, true);
        } catch (\Throwable) {
            return $data;
        }
    }

    /**
     * @param array<mixed> $array
     */
    public static function normalize(array &$array, int $sort_flags = SORT_REGULAR): void
    {
        \ksort($array, $sort_flags);

        foreach ($array as $index => &$arr) {
            if (\is_array($arr)) {
                self::normalize($arr, $sort_flags);
            }

            if (\is_array($arr) && empty($arr)) {
                unset($array[$index]);
            }
        }
    }

    public static function isEmpty(string $string): bool
    {
        if ('' === \trim($string)) {
            return true;
        }

        return empty(\json_decode($string, true, 512, JSON_THROW_ON_ERROR));
    }
}
