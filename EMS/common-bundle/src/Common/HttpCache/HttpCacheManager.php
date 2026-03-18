<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\HttpCache;

use EMS\CommonBundle\Common\HttpClientFactory;
use EMS\Helpers\Html\Headers;
use Psr\Log\LoggerInterface;

class HttpCacheManager
{
    public function __construct(private readonly LoggerInterface $logger, private readonly TagCollector $tagCollector)
    {
    }

    public function purgeByUrl(string $url): int
    {
        $purgedCounter = 0;
        foreach ($this->tagCollector->cacheConfigs() as $cacheConfigs) {
            $client = HttpClientFactory::create(
                baseUrl: $cacheConfigs->url,
                headers: $cacheConfigs->headers,
            );
            $response = $client->request('PURGE', $url);
            if (200 === $response->getStatusCode()) {
                ++$purgedCounter;
            }
        }
        $this->logger->debug(\sprintf('Purging %d HTTP cache(s)', $purgedCounter));

        return $purgedCounter;
    }

    public function purgeAll(): int
    {
        return $this->purgeByTags('.*');
    }

    public function purgeByTags(string ...$tags): int
    {
        $purgedCounter = 0;
        foreach ($this->tagCollector->cacheConfigs() as $cacheConfigs) {
            $client = HttpClientFactory::create(
                baseUrl: $cacheConfigs->url,
                headers: \array_merge($cacheConfigs->headers, [
                    Headers::X_CACHE_TAGS => $tags,
                ]),
            );
            $response = $client->request('BAN');
            if (200 === $response->getStatusCode()) {
                ++$purgedCounter;
            }
        }
        $this->logger->debug(\sprintf('Purging %d HTTP cache(s)', $purgedCounter));

        return $purgedCounter;
    }
}
