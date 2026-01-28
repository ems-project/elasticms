<?php

declare(strict_types=1);

use EMS\CommonBundle\Controller\FileController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return function (RoutingConfigurator $routes): void {
    $routes->add('ems_common_file_view', '/file/view/{sha1}')
        ->controller([FileController::class, 'view'])
        ->methods(['GET']);

    $routes->add('ems_common_file_download', '/file/download/{sha1}')
        ->controller([FileController::class, 'download'])
        ->methods(['GET']);

    $routes->add('ems_asset', '/file/{hash_config}/{hash}/{filename}')
        ->controller([FileController::class, 'asset'])
        ->methods(['GET']);
};
