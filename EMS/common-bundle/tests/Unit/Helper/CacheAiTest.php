<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Unit\Helper;

use EMS\CommonBundle\Helper\Cache;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CacheAiTest extends TestCase
{
    private Cache $cache;

    #[\Override]
    protected function setUp(): void
    {
        $this->cache = new Cache('sha256');
    }

    public function testGenerateEtag(): void
    {
        $response = new Response('Test content');
        $etag = $this->cache->generateEtag($response);

        $this->assertNotNull($etag);
        $this->assertEquals(\hash('sha256', 'Test content'), $etag);
    }

    public function testGenerateEtagWithNonStringContent(): void
    {
        $response = new Response();
        $response->setContent(null);
        $etag = $this->cache->generateEtag($response);

        $this->assertNull($etag);
    }

    public function testMakeResponseCacheable(): void
    {
        $request = new Request();
        $response = new Response();
        $etag = '"test-etag"';
        $lastUpdateDate = new \DateTime('2023-01-01');
        $immutableRoute = true;

        $this->cache->makeResponseCacheable($request, $response, $etag, $lastUpdateDate, $immutableRoute);

        $this->assertEquals($etag, $response->getEtag());
        $this->assertEquals(2_678_400, $response->getMaxAge());
        $this->assertTrue($response->headers->hasCacheControlDirective('immutable'));
        $this->assertEquals($lastUpdateDate, $response->getLastModified());
    }

    public function testMakeResponseCacheableWithoutLastUpdateDate(): void
    {
        $request = new Request();
        $response = new Response();
        $etag = '"test-etag"';
        $immutableRoute = false;

        $this->cache->makeResponseCacheable($request, $response, $etag, null, $immutableRoute);

        $this->assertEquals($etag, $response->getEtag());
        $this->assertEquals(3600, $response->getMaxAge());
        $this->assertFalse($response->headers->hasCacheControlDirective('immutable'));
        $this->assertNull($response->getLastModified());
    }
}
