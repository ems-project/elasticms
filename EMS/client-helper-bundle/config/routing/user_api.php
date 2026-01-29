<?php

declare(strict_types=1);

use EMS\ClientHelperBundle\Controller\UserApi\DocumentController;
use EMS\ClientHelperBundle\Controller\UserApi\FileController;
use EMS\ClientHelperBundle\Controller\UserApi\LoginController;
use EMS\ClientHelperBundle\Controller\UserApi\ProfileController;
use EMS\ClientHelperBundle\Controller\UserApi\TestController;
use EMS\ClientHelperBundle\Controller\UserApi\UserController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return function (RoutingConfigurator $routes): void {
    $routes->add('emsch_user_api_test', '/test')
        ->controller([TestController::class])
        ->methods(['GET']);

    $routes->add('emsch_user_api_login', '/login')
        ->controller([LoginController::class])
        ->methods(['POST']);

    $routes->add('emsch_user_api_users', '/users')
        ->controller([UserController::class, 'index'])
        ->methods(['GET']);

    $routes->add('emsch_user_api_profile', '/profile')
        ->controller([ProfileController::class])
        ->methods(['GET']);

    $routes->add('emsch_user_api_create_file', '/file')
        ->controller([FileController::class, 'create'])
        ->methods(['POST']);

    $routes->add('emsch_user_api_document', '/documents/{contentType}/{ouuid}')
        ->controller([DocumentController::class, 'show'])
        ->methods(['GET']);

    $routes->add('emsch_user_api_create_document', '/documents/{contentType}')
        ->controller([DocumentController::class, 'create'])
        ->methods(['POST']);

    $routes->add('emsch_user_api_update_document', '/documents/{contentType}/{ouuid}')
        ->controller([DocumentController::class, 'update'])
        ->methods(['PUT']);

    $routes->add('emsch_user_api_merge_document', '/documents/{contentType}/{ouuid}')
        ->controller([DocumentController::class, 'merge'])
        ->methods(['PATCH']);
};
