<?php

declare(strict_types=1);

use EMS\ClientHelperBundle\Controller\CoreBridgeController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return function (RoutingConfigurator $routes): void {
    $routes->add('emsch_api', '/emsch/api')
        ->controller([CoreBridgeController::class, 'api'])
        ->methods(['HEAD']);

    $routes->add('emsch_api_version', '/emsch/api/versions')
        ->controller([CoreBridgeController::class, 'getVersions'])
        ->methods(['GET']);

    $routes->add('emsch_api_auto_save', '/emsch/api/autosave/{contentType}/{revisionId}')
        ->controller([CoreBridgeController::class, 'autoSave'])
        ->methods(['POST']);

    $routes->add('emsch_api_file_init', '/emsch/api/file/init-upload')
        ->controller([CoreBridgeController::class, 'fileInitUpload'])
        ->methods(['POST']);

    $routes->add('emsch_api_file_chunk', '/emsch/api/file/chunk/{hash}')
        ->controller([CoreBridgeController::class, 'fileChunk'])
        ->methods(['POST']);
};
