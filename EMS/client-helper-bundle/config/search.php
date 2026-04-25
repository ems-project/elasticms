<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EMS\ClientHelperBundle\Helper\Search\Manager;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->defaults()
        ->private();

    $services->set('emsch.search.manager', Manager::class)
        ->args([service('emsch.manager.client_request')]);
};
