<?php

declare(strict_types=1);

namespace EMS\Helpers\Standard;

class Text
{
    public static function superTrim(string $content): string
    {
        return \trim(\preg_replace('!\s+!', ' ', $content) ?? '');
    }

    public static function humanize(string $str): string
    {
        $str = \trim(\strtolower($str));
        $str = \preg_replace('/\_/', ' ', $str);

        if (\is_string($str)) {
            $str = \preg_replace('/[^a-z0-9\s+\-]/', '', $str);
        }
        if (\is_string($str)) {
            $str = \preg_replace('/\s+/', ' ', $str);
        }
        if (\is_string($str)) {
            $str = \preg_replace('/\-/', ' ', $str);
        }
        if (\is_string($str)) {
            $str = \explode(' ', $str);
        }

        if (!\is_array($str)) {
            throw new \RuntimeException('Humanize failed!');
        }

        $str = \array_map('ucwords', $str);

        return \implode(' ', $str);
    }
}
