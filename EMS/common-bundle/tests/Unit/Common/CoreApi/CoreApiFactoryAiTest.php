<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Unit\Common\CoreApi;

use EMS\CommonBundle\Common\CoreApi\Client;
use EMS\CommonBundle\Common\CoreApi\CoreApi;
use EMS\CommonBundle\Common\CoreApi\CoreApiFactory;
use EMS\CommonBundle\Storage\StorageManager;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class CoreApiFactoryAiTest extends TestCase
{
    private HttpClientInterface $httpClient;
    private LoggerInterface $logger;
    private StorageManager $storageManager;
    private CoreApiFactory $factory;

    #[\Override]
    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->storageManager = $this->createMock(StorageManager::class);
        $this->factory = new CoreApiFactory($this->httpClient, $this->logger, $this->storageManager, [
            'headers' => [],
            'verify' => true,
            'timeout' => 30,
        ]);
    }

    public function testCreate(): void
    {
        $baseUrl = 'http://example.com';
        $coreApi = $this->factory->create($baseUrl);

        $this->assertInstanceOf(CoreApi::class, $coreApi);

        $reflection = new \ReflectionClass($coreApi);
        $clientProperty = $reflection->getProperty('client');
        $client = $clientProperty->getValue($coreApi);

        $this->assertInstanceOf(Client::class, $client);
        $this->assertEquals($baseUrl, $client->getBaseUrl());
    }
}
