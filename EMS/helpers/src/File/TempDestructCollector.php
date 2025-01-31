<?php

declare(strict_types=1);

namespace EMS\Helpers\File;

class TempDestructCollector
{
    /** @var array<string, TempFile|TempDirectory> */
    public static array $collect = [];

    public static function add(TempFile|TempDirectory $temp): void
    {
        self::$collect[$temp->path] = $temp;
    }
}
