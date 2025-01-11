<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Elasticsearch;

class QueryStringEscaper
{
    private const string REGEX_RESERVED_CHARACTERS = '/[\\+\\-\\=\\&\\|\\!\\(\\)\\{\\}\\[\\]\\^\\\"\\~\\*\\<\\>\\?\\:\\\\\\/]/';

    public static function escape(string $queryString): string
    {
        $result = \preg_replace(self::REGEX_RESERVED_CHARACTERS, \addslashes('\\$0'), $queryString);

        return $result ?: $queryString;
    }
}
