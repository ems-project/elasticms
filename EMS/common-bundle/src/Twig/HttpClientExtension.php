<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Twig;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Twig\Attribute\AsTwigFunction;

class HttpClientExtension
{
    public function __construct(private readonly HttpClientInterface $httpClient)
    {
    }

    /**
     * @param array<string, mixed> $options
     */
    #[AsTwigFunction(name: 'ems_http')]
    public function request(string $url, string $method = 'GET', array $options = []): ResponseInterface
    {
        return $this->httpClient->request($method, $url, $options);
    }
}
