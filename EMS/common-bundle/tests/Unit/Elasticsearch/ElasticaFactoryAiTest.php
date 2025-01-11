<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Elasticsearch;

use EMS\CommonBundle\Elasticsearch\Client;
use EMS\CommonBundle\Elasticsearch\ElasticaFactory;
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

    public function testFromConfigDevEnvironment(): void
    {
        $this->factory = new ElasticaFactory($this->logger, 'dev');

        $hosts = ['http://localhost:9200'];
        $client = $this->factory->fromConfig($hosts);

        $this->assertInstanceOf(Client::class, $client);
    }

    public function testFromConfigProdEnvironment(): void
    {
        $this->factory = new ElasticaFactory($this->logger, 'prod');

        $hosts = ['http://localhost:9200'];
        $client = $this->factory->fromConfig($hosts);

        $this->assertInstanceOf(Client::class, $client);
    }

    public function testFromConfigWithCustomConnectionPool(): void
    {
        $this->factory = new ElasticaFactory($this->logger, 'prod');

        $hosts = ['http://localhost:9200'];
        $connectionPool = 'CustomConnectionPool';
        $client = $this->factory->fromConfig($hosts, $connectionPool);

        $this->assertInstanceOf(Client::class, $client);
    }
}
