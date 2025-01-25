<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Cache;

use EMS\ClientHelperBundle\Helper\ContentType\ContentType;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final readonly class CacheHelper
{
    public function __construct(private CacheItemPoolInterface $cache, private LoggerInterface $logger, private string $hashAlgo)
    {
    }

    public function getResponse(string $cacheKey): ?Response
    {
        $cacheItem = $this->cache->getItem($cacheKey);
        if (!$cacheItem->isHit()) {
            return null;
        }

        $this->logger->debug(\sprintf('Using cached response with key %s', $cacheKey));

        $cacheResponse = CacheResponse::fromCache($cacheItem);
        $response = $cacheResponse->getResponse();
        $this->logger->log($response->isSuccessful() ? 'debug' : 'error', $cacheResponse->getLog());

        return $response;
    }

    public function saveResponse(CacheResponse $cacheResponse, string $cacheKey): void
    {
        $item = $this->cache->getItem($cacheKey);
        $item->set($cacheResponse->getData());
        $this->cache->save($item);
    }

    public function getContentType(ContentType $contentType): ?ContentType
    {
        $item = $this->cache->getItem($contentType->getCacheKey());

        if (!$item->isHit()) {
            return null;
        }

        $cachedContentType = $item->get();

        if (!$cachedContentType instanceof ContentType) {
            return null;
        }

        if ($cachedContentType->getEnvironment()->getHash() !== $contentType->getEnvironment()->getHash()) {
            return null; // update on environment
        }

        if ($cachedContentType->getCacheValidityTag() !== $contentType->getCacheValidityTag()) {
            $this->cache->deleteItem($contentType->getCacheKey());

            return null;
        }

        return $cachedContentType;
    }

    public function saveContentType(ContentType $contentType): void
    {
        $item = $this->cache->getItem($contentType->getCacheKey());

        $item->set($contentType);
        $this->cache->save($item);
    }

    public function makeResponseCacheable(Request $request, Response $response): void
    {
        if (!\is_string($response->getContent())) {
            return;
        }

        $response->setCache([
            'etag' => \hash($this->hashAlgo, $response->getContent()),
            'max_age' => 600,
            's_maxage' => 3600,
            'public' => true,
            'private' => false,
        ]);
        $response->isNotModified($request);
    }
}
