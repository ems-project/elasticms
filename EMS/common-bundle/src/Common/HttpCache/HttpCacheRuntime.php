<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\HttpCache;

class HttpCacheRuntime
{
    public function __construct(
        private readonly HttpCacheManager $httpCacheManager,
    ) {
    }

    /**
     * @param string|string[] $urlOrTags
     */
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
}
