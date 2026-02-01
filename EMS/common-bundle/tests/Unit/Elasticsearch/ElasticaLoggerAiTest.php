<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Elasticsearch;

use Elastica\Response;
use EMS\CommonBundle\Elasticsearch\ElasticaLogger;
use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class ElasticaLoggerAiTest extends TestCase
{
    private LoggerInterface $logger;
    private ElasticaLogger $elasticaLogger;

    #[\Override]
    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->elasticaLogger = new ElasticaLogger($this->logger, true);
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testEnableDisable(): void
    {
        $this->elasticaLogger->disable();
        $this->assertFalse($this->elasticaLogger->isEnabled());

        $this->elasticaLogger->enable();
        $this->assertTrue($this->elasticaLogger->isEnabled());
    }

    public function testLogQuery(): void
    {
        $path = 'test_path';
        $method = 'GET';
        $data = ['key' => 'value'];

        $request = new ServerRequest($method, $path, [], '{"key":"value"}');

        $this->logger->expects($this->once())->method('info')->with(
            $this->stringContains($path),
            $this->equalTo([$data])
        );

        $this->elasticaLogger->logResponse($request, new Response(''));

        $this->assertSame(1, $this->elasticaLogger->getNbQueries());
        $queries = $this->elasticaLogger->getQueries();
        $this->assertSame($path, $queries[0]['path']);
        $this->assertSame($method, $queries[0]['method']);
        $this->assertSame($data, $queries[0]['data'][0]);
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testReset(): void
    {
        $request = new ServerRequest('/test_path', 'GET', ['key' => 'value']);

        $this->elasticaLogger->logResponse($request, new Response(''));
        $this->assertSame(1, $this->elasticaLogger->getNbQueries());

        $this->elasticaLogger->reset();
        $this->assertSame(0, $this->elasticaLogger->getNbQueries());
    }

    public function testLog(): void
    {
        $this->logger->expects($this->once())->method('log')->with(
            $this->equalTo('info'),
            $this->equalTo('Test message'),
            $this->equalTo(['context' => 'test'])
        );

        $this->elasticaLogger->log('info', 'Test message', ['context' => 'test']);
    }
}
