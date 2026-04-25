<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EMS\AdminUIBundle\Helper\Asset\AssetVersionStrategy;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->defaults()
        ->private();

    $services->set('emsadminui.helper.asset_version_strategy', AssetVersionStrategy::class)
        ->args([
            service('file_locator'),
            service('ems.vite'),
        ])
        ->tag('twig.runtime');
};
