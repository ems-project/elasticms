<?php

declare(strict_types=1);

use EMS\CommonBundle\Controller\FileController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return function (RoutingConfigurator $routes): void {
    $routes->add('ems_asset', '/file/{hash_config}/{hash}/{filename}')
        ->controller([FileController::class, 'asset'])
        ->methods(['GET']);
};
