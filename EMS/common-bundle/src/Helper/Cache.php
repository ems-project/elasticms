<?php

namespace EMS\CommonBundle\Helper;

use Symfony\Component\HttpFoundation\Response;

class Cache
{
    public function __construct(private readonly string $hashAlgo)
    {
    }

    public function generateEtag(Response $response): ?string
    {
        if (!\is_string($response->getContent())) {
            return null;
        }

        return \hash($this->hashAlgo, $response->getContent());
    }

    public function makeResponseCacheable(Response $response, string $etag, ?\DateTime $lastUpdateDate, bool $immutableRoute): void
    {
        $response->setCache([
            'etag' => $etag,
            'max_age' => $immutableRoute ? 604800 : 600,
            's_maxage' => $immutableRoute ? 2_678_400 : 3600,
            'public' => true,
            'private' => false,
            'immutable' => $immutableRoute,
        ]);

        if (null !== $lastUpdateDate) {
            $response->setLastModified($lastUpdateDate);
        }
    }
}
