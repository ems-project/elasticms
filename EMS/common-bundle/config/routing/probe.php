<?php

declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return function (RoutingConfigurator $routes): void {
    $routes->add('ems_probe_readiness', '/_readiness')
        ->controller('ems_common.controller.probe::readiness')
        ->host('probe.localhost')
        ->methods(['GET']);

    $routes->add('ems_probe_liveness', '/_liveness')
        ->controller('ems_common.controller.probe::liveness')
        ->host('probe.localhost')
        ->methods(['GET']);
};
