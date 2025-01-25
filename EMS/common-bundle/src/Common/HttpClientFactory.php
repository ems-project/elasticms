<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common;

use GuzzleHttp\Client;

class HttpClientFactory
{
    /**
     * @param array<mixed> $headers
     */
    public static function create(string $baseUrl, array $headers = [], int $timeout = 30, bool $allowRedirects = false): Client
    {
        return new Client([
            'base_uri' => $baseUrl,
            'headers' => $headers,
            'timeout' => $timeout,
            'allow_redirects' => $allowRedirects,
        ]);
    }
}
