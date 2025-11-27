<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\HttpCache;

class HttpCacheRuntime
{
    public function __construct(
        private readonly HttpCacheManager $httpCacheManager,
    ) {
    }

    public function clearCaches(): void
    {
        $this->httpCacheManager->purgeAll();
    }
}
