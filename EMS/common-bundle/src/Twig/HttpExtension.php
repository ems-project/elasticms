<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Twig;

use EMS\CommonBundle\Common\HttpCache\HttpCacheManager;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Twig\Attribute\AsTwigFunction;

class HttpExtension
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly HttpCacheManager $httpCacheManager,
    ) {
    }

    /**
     * @param string|string[] $urlOrTags
     */
    #[AsTwigFunction(name: 'ems_clear_http_caches')]
    public function clearCaches(array|string $urlOrTags = []): void
    {
        if (empty($urlOrTags)) {
            $this->httpCacheManager->purgeAll();
        } elseif (\is_string($urlOrTags) && \str_starts_with($urlOrTags, '/')) {
            $this->httpCacheManager->purgeByUrl($urlOrTags);
        } else {
            if (!\is_array($urlOrTags)) {
                $urlOrTags = [$urlOrTags];
            }
            $this->httpCacheManager->purgeByTags(...$urlOrTags);
        }
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
