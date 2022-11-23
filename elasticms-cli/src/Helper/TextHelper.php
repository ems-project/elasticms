<?php

namespace App\Helper;

class TextHelper
{
    public static function trim(string $content): string
    {
        return \trim(\preg_replace('!\s+!', ' ', $content) ?? '');
    }
}
