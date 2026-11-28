<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Elasticsearch;

use EMS\CommonBundle\Elasticsearch\Client;
use EMS\CommonBundle\Elasticsearch\ElasticaFactory;
use EMS\CommonBundle\Elasticsearch\ElasticaLogger;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

final class ElasticaFactoryAiTest extends TestCase
{
    /**
     * @var Stub&ElasticaLogger
     */
    private Stub $logger;
    private ElasticaFactory $factory;

    #[\Override]
    protected function setUp(): void
    {
        $this->logger = $this->createStub(ElasticaLogger::class);
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testFromConfig(): void
    {
        $this->factory = new ElasticaFactory($this->logger);

        $hosts = ['http://localhost:9200'];
        $client = $this->factory->fromConfig($hosts);

        $this->assertInstanceOf(Client::class, $client);
    }
}
