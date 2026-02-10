<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Request;

use Symfony\Component\HttpFoundation\Request;

final class RequestHelper
{
    private const string PATTERN = '/%(?<parameter>(_|)[[:alnum:]_]*)%/m';

    public static function replace(Request $request, string $subject): string
    {
        $all = [...$request->query->all(), ...$request->attributes->all()];
        $result = \preg_replace_callback(self::PATTERN, fn ($match) => $all[$match['parameter']] ?? $match[0], $subject);

        if (!\is_string($result)) {
            throw new \RuntimeException(\sprintf('replace request failed for subject %s', $subject));
        }

        return $result;
    }
}
