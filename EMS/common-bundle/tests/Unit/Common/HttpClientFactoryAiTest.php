<?php

namespace EMS\Tests\CommonBundle\Common;

use EMS\CommonBundle\Common\HttpClientFactory;
use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;

class HttpClientFactoryAiTest extends TestCase
{
    public function testCreate(): void
    {
        $baseUrl = 'http://example.com';
        $headers = ['User-Agent' => 'EMSClient'];
        $timeout = 10;
        $allowRedirects = true;

        $client = HttpClientFactory::create($baseUrl, $headers, $timeout, $allowRedirects);

        $this->assertInstanceOf(Client::class, $client);

        $config = $client->getConfig();

        $this->assertEquals($baseUrl, $config['base_uri']);
        $this->assertEquals($headers, $config['headers']);
        $this->assertEquals($timeout, $config['timeout']);
        $this->assertEquals($allowRedirects, $config['allow_redirects']);
    }
}
