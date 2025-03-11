<?php

declare(strict_types=1);

namespace EMS\Helpers\Date;

class DateFormatHelper
{
    protected const JAVA_TO_PHP = [
        'dd' => 'd',  // 2-digit day
        'MM' => 'm',  // 2-digit month
        'yyyy' => 'Y',  // 4-digit year
        'hh' => 'g',  // 12-hour format (1-12)
        'HH' => 'G',  // 24-hour format (0-23)
        'mm' => 'i',  // Minutes
        'ss' => 's',  // Seconds
        'aa' => 'A',  // AM/PM
    ];

    protected const JS_TO_PHP = [
        'yyyy' => 'Y',  // 4-digit year
        'yy' => 'y',  // 2-digit year
        'DD' => 'l',  // Full weekday name (Monday, Tuesday, ...)
        'D' => 'D',  // Short weekday name (Mon, Tue, ...)
        'dd' => 'd',  // 2-digit day
        'mm' => 'm',  // 2-digit month
        'MM' => 'F',  // Full month name (January, February, ...)
        'M' => 'M',  // Short month name (Jan, Feb, ...)
    ];

    public static function convert(string $type, string $format): string
    {
        $replacements = match ($type) {
            'java' => self::JAVA_TO_PHP,
            'js' => self::JS_TO_PHP,
            default => throw new \RuntimeException(\sprintf('Invalid type "%s" use (java or js)', $type)),
        };

        return \strtr($format, $replacements);
    }
}
