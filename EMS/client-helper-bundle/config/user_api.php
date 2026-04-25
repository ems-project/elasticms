<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EMS\ClientHelperBundle\Controller\UserApi\DocumentController;
use EMS\ClientHelperBundle\Controller\UserApi\FileController;
use EMS\ClientHelperBundle\Controller\UserApi\LoginController;
use EMS\ClientHelperBundle\Controller\UserApi\ProfileController;
use EMS\ClientHelperBundle\Controller\UserApi\TestController;
use EMS\ClientHelperBundle\Controller\UserApi\UserController;
use EMS\ClientHelperBundle\Helper\UserApi\AuthService;
use EMS\ClientHelperBundle\Helper\UserApi\ClientFactory;
use EMS\ClientHelperBundle\Helper\UserApi\DocumentService;
use EMS\ClientHelperBundle\Helper\UserApi\FileService;
use EMS\ClientHelperBundle\Helper\UserApi\TestService;
use EMS\ClientHelperBundle\Helper\UserApi\UserService;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->set('emsch.user_api.client_factory', ClientFactory::class)
        ->private()
        ->args(['%emsch.user_api.url%']);

    $services->set('emsch.user_api.auth', AuthService::class)
        ->private()
        ->args([service('emsch.user_api.client_factory')]);

    $services->set('emsch.user_api.document', DocumentService::class)
        ->private()
        ->args([service('emsch.user_api.client_factory')]);

    $services->set('emsch.user_api.file', FileService::class)
        ->private()
        ->args([
            service('emsch.user_api.client_factory'),
            service('logger'),
        ]);

    $services->set('emsch.user_api.test', TestService::class)
        ->private()
        ->args([
            service('emsch.user_api.client_factory'),
            service('logger'),
        ]);

    $services->set('emsch.user_api.user', UserService::class)
        ->private()
        ->args([service('emsch.user_api.client_factory')]);

    $services->set(DocumentController::class)
        ->public()
        ->args([service('emsch.user_api.document')]);

    $services->set(FileController::class)
        ->public()
        ->args([service('emsch.user_api.file')]);

    $services->set(LoginController::class)
        ->public()
        ->args([service('emsch.user_api.auth')]);

    $services->set(ProfileController::class)
        ->public()
        ->args([service('emsch.user_api.user')]);

    $services->set(TestController::class)
        ->public()
        ->args([service('emsch.user_api.test')]);

    $services->set(UserController::class)
        ->public()
        ->args([service('emsch.user_api.user')]);
};
