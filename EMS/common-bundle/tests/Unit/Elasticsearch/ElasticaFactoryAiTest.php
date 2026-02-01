<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Elasticsearch;

use EMS\CommonBundle\Elasticsearch\Client;
use EMS\CommonBundle\Elasticsearch\ElasticaFactory;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class ElasticaFactoryAiTest extends TestCase
{
    private LoggerInterface $logger;
    private ElasticaFactory $factory;

    #[\Override]
    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testFromConfigDevEnvironment(): void
    {
        $this->factory = new ElasticaFactory($this->logger, 'dev');

        $hosts = ['http://localhost:9200'];
        $client = $this->factory->fromConfig($hosts);

        $this->assertInstanceOf(Client::class, $client);
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testFromConfigProdEnvironment(): void
    {
        $this->factory = new ElasticaFactory($this->logger, 'prod');

        $hosts = ['http://localhost:9200'];
        $client = $this->factory->fromConfig($hosts);

        $this->assertInstanceOf(Client::class, $client);
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testFromConfigWithCustomConnectionPool(): void
    {
        $this->factory = new ElasticaFactory($this->logger, 'prod');

        $hosts = ['http://localhost:9200'];
        $client = $this->factory->fromConfig($hosts);

        $this->assertInstanceOf(Client::class, $client);
    }
}
