<?php

declare(strict_types=1);

namespace EMS\Helpers\Standard;

class Number
{
    public static function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = \max($bytes, 0);

        $pow = \floor(($bytes ? \log($bytes) : 0) / \log(1024));
        $pow = \min($pow, \count($units) - 1);

        $bytes /= 1024 ** $pow;

        return \round($bytes, $precision).' '.$units[$pow];
    }
}
