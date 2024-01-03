<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Unit\Helper;

use EMS\CommonBundle\Helper\Cache;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class CacheTest extends TestCase
{
    private Cache $cache;

    private Response $response;

    protected function setUp(): void
    {
        $hashAlgo = 'sha1'; // Maybe SHA-256 -> sha("Secret Key" + "Timestamp" + signed message)
        $this->cache = new Cache($hashAlgo);
        $this->response = new Response();
        parent::setUp();
    }

    public function testGenerateEtagShouldReturnNull(): void
    {
        $this->response->setContent(null);
        self::assertNull($this->cache->generateEtag($this->response));
    }

    public function testGenerateEtagShouldReturnHash(): void
    {
        $this->response->setContent('test');
        self::assertSame('a94a8fe5ccb19ba61c4c0873d391e987982fbbd3', $this->cache->generateEtag($this->response));
    }

    public function testMakeResponseCacheableReturnSameEtag(): void
    {
        $this->cache->makeResponseCacheable($this->response, 'test', null, false);
        self::assertSame('"test"', $this->response->getEtag());
    }

    public function testMakeResponseCacheableReturnSameMaxAgeFalse(): void
    {
        $this->cache->makeResponseCacheable($this->response, 'test', null, false);
        self::assertSame(3600, $this->response->getMaxAge());
    }

    public function testMakeResponseCacheableReturnSameMaxAgeTrue(): void
    {
        $this->cache->makeResponseCacheable($this->response, 'test', null, true);
        self::assertSame(2_678_400, $this->response->getMaxAge());
    }

    public function testMakeResponseCacheableReturnSameLastUpdateDateNotNull(): void
    {
        $this->cache->makeResponseCacheable($this->response, 'test', null, false);
        self::assertSame(null, $this->response->getLastModified());
    }

    public function testMakeResponseCacheableReturnSameImmutableRoute(): void
    {
        $this->cache->makeResponseCacheable($this->response, 'test', null, false);
        self::assertSame(true, !$this->response->isImmutable());
    }
}
