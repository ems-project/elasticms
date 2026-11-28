<?php

declare(strict_types=1);

use EMS\CommonBundle\Controller\FileController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return function (RoutingConfigurator $routes): void {
    $routes->add('ems_asset_in_archive', '/bundles/{hash}/{path}')
        ->controller([FileController::class, 'assetInArchive'])
        ->methods(['GET'])
        ->requirements([
            'path' => '.+',
        ]);
};
