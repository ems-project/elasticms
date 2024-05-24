<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\Route;

use EMS\CommonBundle\Controller\MetricController;
use EMS\CommonBundle\Routes;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

final class Loader
{
    public function __construct(private readonly bool $metricEnabled)
    {
    }

    public function load(): RouteCollection
    {
        $commonRouteCollection = new RouteCollection();

        if ($this->metricEnabled) {
            $metricRoute = new Route(Routes::METRICS->value);
            $metricRoute->setMethods(['GET']);
            $metricRoute->setHost('%ems.metric.host%');
            $metricRoute->setDefault('_controller', MetricController::METRICS);
            $commonRouteCollection->add('ems_metric', $metricRoute);
        }

        return $commonRouteCollection;
    }
}
