<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Helper;

use EMS\Helpers\Html\Headers;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Cache
{
    public function __construct(private readonly string $hashAlgo)
    {
    }

    public function generateEtag(Response $response): ?string
    {
        $content = $response->getContent();
        if (false === $content || '' === $content) {
            return null;
        }

        return \hash($this->hashAlgo, $content);
    }

    public function makeResponseCacheable(Request $request, Response $response, string $etag, ?\DateTime $lastUpdateDate, bool $immutableRoute, int $maxAge = 600): void
    {
        $rewritedEtags = [];
        foreach ($request->getETags() as $requestEtag) {
            $rewritedEtags[] = \preg_replace('/\-gzip"$/i', '"', (string) $requestEtag);
        }
        $request->headers->replace([Headers::IF_NONE_MATCH => $rewritedEtags]);
        $response->setCache([
            'etag' => $etag,
            'max_age' => $immutableRoute ? 604800 : $maxAge,
            's_maxage' => $immutableRoute ? 2_678_400 : ($maxAge * 6),
            'public' => true,
            'private' => false,
            'immutable' => $immutableRoute,
        ]);

        if (null !== $lastUpdateDate) {
            $response->setLastModified($lastUpdateDate);
        }
    }
}
