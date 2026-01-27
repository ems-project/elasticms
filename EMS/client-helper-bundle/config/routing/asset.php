<?php

declare(strict_types=1);

use EMS\ClientHelperBundle\Controller\AssetController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return function (RoutingConfigurator $routes): void {
    $routes->add('ems_core_asset_proxy', '/emsch_assets/{requestPath}')
        ->controller([AssetController::class, 'proxyToEnvironmentAlias'])
        ->methods(['GET'])
        ->requirements([
            'requestPath' => '.+',
        ]);
};
