<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\HttpCache;

readonly class HttpCacheConfig
{
    /**
     * @param string[]|string[][] $headers
     */
    public function __construct(
        public string $header,
        public string $url,
        public array $headers,
        public bool $verifySsl,
    ) {
    }
}
