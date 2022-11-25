<?php

namespace EMS\Helpers\Standard;

class Text
{
    public static function superTrim(string $content): string
    {
        return \trim(\preg_replace('!\s+!', ' ', $content) ?? '');
    }
}
