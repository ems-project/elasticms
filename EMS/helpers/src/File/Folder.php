<?php

declare(strict_types=1);

namespace EMS\Helpers\File;

class Folder
{
    public static function getRealPath(string $path): string
    {
        $realPath = \realpath($path);
        if (\is_string($realPath) && \is_dir($realPath)) {
            return $realPath;
        }

        if (false === \mkdir($path, 0o777, true)) {
            throw new \RuntimeException(\sprintf('The path %s can\'t be created', $path));
        }

        $realPath = \realpath($path);
        if (\is_string($realPath)) {
            return $realPath;
        }
        throw new \RuntimeException(\sprintf('The path %s parameter can\'t be converted into a real path nor created', $path));
    }

    public static function createFileDirectories(string $filename): string
    {
        $realFilename = \realpath($filename);
        if (\is_string($realFilename)) {
            return $realFilename;
        }
        $token = '/' === DIRECTORY_SEPARATOR ? '\\' : '/';
        $slugs = \explode(DIRECTORY_SEPARATOR, \str_replace($token, DIRECTORY_SEPARATOR, $filename));
        $basename = \array_pop($slugs);
        if (!\is_string($basename)) {
            throw new \RuntimeException(\sprintf('Invalid basename for the filename %s', $filename));
        }
        $path = \implode(DIRECTORY_SEPARATOR, $slugs);
        $path = self::getRealPath($path);

        return \implode(DIRECTORY_SEPARATOR, [$path, $basename]);
    }
}
