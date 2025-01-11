<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Request;

use Symfony\Component\HttpFoundation\Request;

final class RequestHelper
{
    private const string PATTERN = '/%(?<parameter>(_|)[[:alnum:]_]*)%/m';

    public static function replace(Request $request, string $subject): string
    {
        $subject = self::replaceLocale($subject, $request->getLocale());

        $result = \preg_replace_callback(self::PATTERN, fn ($match) => $request->get($match['parameter'], $match[0]), $subject);

        if (!\is_string($result)) {
            throw new \RuntimeException(\sprintf('replace request failed for subject %s', $subject));
        }

        return $result;
    }

    private static function replaceLocale(string $subject, string $locale): string
    {
        if (\strpos($subject, '%locale%')) {
            @\trigger_error('%locale% is deprecated please use %_locale%', E_USER_DEPRECATED);

            return \str_replace('%locale%', $locale, $subject);
        }

        return $subject;
    }
}
