<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\Metric;

use EMS\CommonBundle\Common\Composer\ComposerInfo;
use EMS\Helpers\Standard\DateTime;
use Prometheus\CollectorRegistry;

final readonly class EmsInfoMetricCollector implements MetricCollectorInterface
{
    public function __construct(private ComposerInfo $composerInfo)
    {
    }

    #[\Override]
    public function getName(): string
    {
        return 'ems_info';
    }

    #[\Override]
    public function validUntil(): int
    {
        return DateTime::create('+1 day')->getTimestamp();
    }

    #[\Override]
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
