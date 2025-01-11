<?php

declare(strict_types=1);

namespace EMS\Tests\CommonBundle\Unit\DataCollector;

use EMS\CommonBundle\DataCollector\ElasticaDataCollector;
use EMS\CommonBundle\Elasticsearch\ElasticaLogger;
use EMS\CommonBundle\Service\ElasticaService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ElasticaDataCollectorAiTest extends TestCase
{
    private ElasticaLogger $logger;
    private ElasticaService $elasticaService;
    private ElasticaDataCollector $collector;

    #[\Override]
    protected function setUp(): void
    {
        $this->logger = $this->createMock(ElasticaLogger::class);
        $this->elasticaService = $this->createMock(ElasticaService::class);
        $this->collector = new ElasticaDataCollector($this->logger, $this->elasticaService);
    }

    public function testCollect(): void
    {
        $this->logger->expects($this->once())
            ->method('getNbQueries')
            ->willReturn(5);
        $this->logger->expects($this->once())
            ->method('getQueries')
            ->willReturn([
                ['engineMS' => 10, 'executionMS' => 5],
                ['engineMS' => 20, 'executionMS' => 10],
            ]);
        $this->elasticaService->expects($this->once())
            ->method('getVersion')
            ->willReturn('7.10.0');
        $this->elasticaService->expects($this->once())
            ->method('getHealthStatus')
            ->willReturn('green');

        $this->collector->collect(new Request(), new Response());

        $this->assertEquals('green', $this->collector->getHealth());
        $this->assertEquals('7.10.0', $this->collector->getVersion());
        $this->assertEquals(5, $this->collector->getQueryCount());
        $this->assertEquals([
            ['engineMS' => 10, 'executionMS' => 5],
            ['engineMS' => 20, 'executionMS' => 10],
        ], $this->collector->getQueries());
        $this->assertEquals(30, $this->collector->getTime());
        $this->assertEquals(15.0, $this->collector->getExecutionTime());
    }

    public function testName(): void
    {
        $this->assertEquals('elastica', $this->collector->getName());
    }

    public function testReset(): void
    {
        $this->logger->expects($this->once())->method('reset');
        $this->collector->reset();
    }
}
