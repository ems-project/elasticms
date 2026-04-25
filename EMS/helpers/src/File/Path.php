<?php

declare(strict_types=1);

namespace EMS\Helpers\File;

final class Path
{
    public static function isAbsolutePath(string $path): bool
    {
        if ('' === $path) {
            return false;
        }

        if ('/' === $path[0] || '\\' === $path[0]) {
            return true;
        }

        return 1 === \preg_match('/^[A-Za-z]:[\/\\\\]/', $path);
    }

    public static function isRelativePath(string $path): bool
    {
        return '' !== $path && !self::isAbsolutePath($path);
    }

    public static function join(string ...$parts): string
    {
        if ([] === $parts) {
            return '';
        }

        $normalized = [];
        foreach ($parts as $index => $part) {
            if ('' === $part) {
                continue;
            }

            $part = \str_replace('\\', '/', $part);

            if (0 === $index) {
                $normalized[] = \rtrim($part, '/');
                continue;
            }

            $normalized[] = \trim($part, '/');
        }

        if ([] === $normalized) {
            return '';
        }

        $path = \implode('/', \array_filter($normalized, static fn (string $part): bool => '' !== $part));

        if ('' === $path && self::isAbsolutePath($parts[0])) {
            return \str_replace('\\', '/', $parts[0][0]);
        }

        return $path;
    }
}
