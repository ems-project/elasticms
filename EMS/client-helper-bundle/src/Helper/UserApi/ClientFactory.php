<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\UserApi;

use GuzzleHttp\Client;

/**
 * @todo use EMS\CommonBundle\Contracts\CoreApi\CoreApiInterface
 */
final class ClientFactory
{
    private string $baseUrl;

    public function __construct(string $baseUrl)
    {
        $this->baseUrl = $baseUrl;
    }

    /**
     * @param array<string, string|null> $headers
     */
    public function createClient(array $headers = []): Client
    {
        return new Client([
            'base_uri' => $this->baseUrl,
            'headers' => $headers,
            'timeout' => 30,
            'allow_redirects' => false,
        ]);
    }
}
