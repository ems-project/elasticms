<?php

declare(strict_types=1);

namespace EMS\Helpers\Standard;

class Locale
{
    public static function short(?string $locale = null, ?string $default = 'en'): string
    {
        $default ??= 'en';
        $result = null !== $locale ? \strtolower($locale) : $default;
        $explode = \explode('_', $result);

        $result = \array_shift($explode);

        return 2 === \strlen($result) ? $result : $default;
    }
}
