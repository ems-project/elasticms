<?php

declare(strict_types=1);

namespace EMS\Tests\CommonBundle\Unit\Controller;

use EMS\CommonBundle\Common\Metric\MetricCollector;
use EMS\CommonBundle\Controller\MetricController;
use PHPUnit\Framework\TestCase;
use Prometheus\RenderTextFormat;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class MetricControllerAiTest extends TestCase
{
    private MetricCollector $metricCollector;
    private ?string $metricPort;
    private MetricController $controller;

    #[\Override]
    protected function setUp(): void
    {
        $this->metricCollector = $this->createMock(MetricCollector::class);
        $this->metricPort = '8080';
        $this->controller = new MetricController($this->metricCollector, $this->metricPort);
    }

    public function testMetricsWithMismatchedPort(): void
    {
        $request = new Request();
        $request->server->set('SERVER_PORT', '8081');

        $this->expectException(NotFoundHttpException::class);

        $this->controller->metrics($request);
    }

    public function testMetricsWithMatchingPort(): void
    {
        $metrics = [];
        $this->metricCollector->expects($this->once())
            ->method('getMetrics')
            ->willReturn($metrics);

        $renderFormat = new RenderTextFormat();
        $content = $renderFormat->render($metrics);

        $request = new Request();
        $request->server->set('SERVER_PORT', '8080');

        $response = $this->controller->metrics($request);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals(RenderTextFormat::MIME_TYPE, $response->headers->get('Content-type'));
        $this->assertEquals($content, $response->getContent());
    }
}
