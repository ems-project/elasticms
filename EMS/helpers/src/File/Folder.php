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

        if (false === \mkdir($path, 0777, true)) {
            throw new \RuntimeException(\sprintf('The path %s can\'t be created', $path));
        }

        $realPath = \realpath($path);
        if (\is_string($realPath)) {
            return $realPath;
        }
        throw new \RuntimeException(\sprintf('The path %s parameter can\'t be converted into a real path nor created', $path));
    }
}
