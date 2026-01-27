<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EMS\ClientHelperBundle\Controller\ApiController;
use EMS\ClientHelperBundle\Helper\Api\ApiService;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->defaults()
        ->private();

    $services->set('emsch.api', ApiService::class)
        ->args([
            service('twig'),
            service(UrlGeneratorInterface::class),
            service('security.helper'),
            tagged_iterator('emsch.client_request.api'),
            tagged_iterator('emsch.api_client'),
        ]);

    $services->set('emsch.controller.api', ApiController::class)
        ->public()
        ->args([
            service('emsch.api'),
            service('emsch.helper_hashcash'),
        ]);

    $services->alias(ApiController::class, 'emsch.controller.api')
        ->public();
};
