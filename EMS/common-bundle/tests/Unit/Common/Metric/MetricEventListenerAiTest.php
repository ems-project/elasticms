<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Unit\Common\Metric;

use EMS\CommonBundle\Common\Metric\MetricCollector;
use EMS\CommonBundle\Common\Metric\MetricEventListener;
use EMS\CommonBundle\Controller\MetricController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class MetricEventListenerAiTest extends TestCase
{
    private MetricEventListener $metricEventListener;
    private MetricCollector $metricCollector;

    #[\Override]
    protected function setUp(): void
    {
        $this->metricCollector = $this->createMock(MetricCollector::class);
        $this->metricEventListener = new MetricEventListener($this->metricCollector);
    }

    public function testGetSubscribedEvents(): void
    {
        $expectedEvents = [
            'kernel.terminate' => [
                ['metricCollect', 300],
            ],
        ];

        $this->assertSame($expectedEvents, MetricEventListener::getSubscribedEvents());
    }

    public function testMetricCollect(): void
    {
        $request = new Request();
        $request->attributes->set('_controller', MetricController::METRICS);

        $event = new TerminateEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            $this->createMock(Response::class)
        );

        $this->metricCollector->expects($this->once())
            ->method('isInMemoryStorage')
            ->willReturn(false);

        $this->metricCollector->expects($this->once())
            ->method('collectWithValidity');

        $this->metricEventListener->metricCollect($event);
    }
}
