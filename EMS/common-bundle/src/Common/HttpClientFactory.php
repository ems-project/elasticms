<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;

class HttpClientFactory
{
    /**
     * @param array<mixed> $headers
     */
    public static function create(string $baseUrl, array $headers = [], int $timeout = 30, bool $allowRedirects = false, ?string $socketPath = null): Client
    {
        $config = [
            'base_uri' => $baseUrl,
            'headers' => $headers,
            'timeout' => $timeout,
            'allow_redirects' => $allowRedirects,
        ];
        if (null !== $socketPath) {
            $handler = new CurlHandler();
            $stack = HandlerStack::create($handler);
            $config['handler'] = $stack;
            $config['curl'] = [
                CURLOPT_UNIX_SOCKET_PATH => $socketPath,
            ];
        }

        return new Client($config);
    }
}
