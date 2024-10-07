<?php

declare(strict_types=1);

namespace EMS\Helpers\Standard;

class Locale
{
    public static function getLanguage(?string $locale = null, ?string $default = 'en'): string
    {
        $default ??= 'en';
        $result = null !== $locale ? \strtolower($locale) : $default;
        $explode = \explode('_', $result);

        $result = \array_shift($explode);

        if (2 !== \strlen($result)) {
            throw new \RuntimeException(\sprintf('Invalid locale passed "%s"', $result));
        }

        return $result;
    }
}
