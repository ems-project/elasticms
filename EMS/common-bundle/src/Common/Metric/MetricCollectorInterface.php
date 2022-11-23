<?php

namespace EMS\CommonBundle\Common\Metric;

use Prometheus\CollectorRegistry;

interface MetricCollectorInterface
{
    public function getName(): string;

    public function collect(CollectorRegistry $collectorRegistry): void;

    public function validUntil(): int;
}
