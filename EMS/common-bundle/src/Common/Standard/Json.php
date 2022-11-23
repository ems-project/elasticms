<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\Standard;

final class Json
{
    /**
     * @param mixed $value
     */
    public static function encode($value, bool $pretty = false): string
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

        return Json::decode($content);
    }

    public static function isJson(string $string): bool
    {
        \json_decode($string);

        return JSON_ERROR_NONE === \json_last_error();
    }
}
