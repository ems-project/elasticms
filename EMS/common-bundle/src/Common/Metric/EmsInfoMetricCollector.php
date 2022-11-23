<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\Metric;

use EMS\CommonBundle\Common\Composer\ComposerInfo;
use EMS\CommonBundle\Common\Standard\DateTime;
use Prometheus\CollectorRegistry;

final class EmsInfoMetricCollector implements MetricCollectorInterface
{
    private ComposerInfo $composerInfo;

    public function __construct(ComposerInfo $composerInfo)
    {
        $this->composerInfo = $composerInfo;
    }

    public function getName(): string
    {
        return 'ems_info';
    }

    public function validUntil(): int
    {
        return DateTime::create('+1 day')->getTimestamp();
    }

    public function collect(CollectorRegistry $collectorRegistry): void
    {
        $versionPackages = $this->composerInfo->getVersionPackages();

        $gauge = $collectorRegistry->getOrRegisterGauge(
            'ems',
            'info',
            'Info ems versions',
            \array_keys($versionPackages)
        );

        $gauge->set(1, \array_values($versionPackages));
    }
}
