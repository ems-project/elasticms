<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\Route;

use EMS\CommonBundle\Controller\MetricController;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

final class Loader
{
    private bool $metricEnabled;

    public function __construct(bool $metricEnabled)
    {
        $this->metricEnabled = $metricEnabled;
    }

    public function load(): RouteCollection
    {
        $commonRouteCollection = new RouteCollection();

        if ($this->metricEnabled) {
            $metricRoute = new Route('/metrics');
            $metricRoute->setMethods(['GET']);
            $metricRoute->setHost('%ems.metric.host%');
            $metricRoute->setDefault('_controller', MetricController::METRICS);
            $commonRouteCollection->add('ems_metric', $metricRoute);
        }

        return $commonRouteCollection;
    }
}
