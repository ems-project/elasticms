<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Unit\Common\Metric;

use EMS\CommonBundle\Common\Composer\ComposerInfo;
use EMS\CommonBundle\Common\Metric\EmsInfoMetricCollector;
use PHPUnit\Framework\TestCase;
use Prometheus\CollectorRegistry;
use Prometheus\Gauge;

class EmsInfoMetricCollectorAiTest extends TestCase
{
    private EmsInfoMetricCollector $emsInfoMetricCollector;
    private ComposerInfo $composerInfo;

    #[\Override]
    protected function setUp(): void
    {
        $this->composerInfo = $this->createMock(ComposerInfo::class);
        $this->emsInfoMetricCollector = new EmsInfoMetricCollector($this->composerInfo);
    }

    public function testGetName(): void
    {
        $this->assertSame('ems_info', $this->emsInfoMetricCollector->getName());
    }

    public function testValidUntil(): void
    {
        $timestamp = $this->emsInfoMetricCollector->validUntil();
        $this->assertGreaterThan(\time(), $timestamp);
    }

    public function testCollect(): void
    {
        $versionPackages = ['package1' => '1.0.0', 'package2' => '2.0.0'];
        $this->composerInfo->method('getVersionPackages')->willReturn($versionPackages);

        $gauge = $this->createMock(Gauge::class);
        $gauge->expects($this->once())->method('set')->with(1, \array_values($versionPackages));

        $collectorRegistry = $this->createMock(CollectorRegistry::class);
        $collectorRegistry->method('getOrRegisterGauge')->willReturn($gauge);

        $this->emsInfoMetricCollector->collect($collectorRegistry);
    }
}
