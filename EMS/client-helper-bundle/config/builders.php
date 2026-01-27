<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EMS\ClientHelperBundle\Helper\Builder\AbstractBuilder;
use EMS\ClientHelperBundle\Helper\Builder\Builders;
use EMS\ClientHelperBundle\Helper\Routing\RoutingBuilder;
use EMS\ClientHelperBundle\Helper\Templating\TemplateBuilder;
use EMS\ClientHelperBundle\Helper\Translation\TranslationBuilder;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->set('emsch.helper.builder', AbstractBuilder::class)
        ->private()
        ->abstract()
        ->args([
            service('emsch.manager.client_request'),
            service('logger'),
            '%emsch.locales%',
            '%emsch.search_limit%',
        ]);

    $services->set('emsch.helper.builders', Builders::class)
        ->args([
            service('emsch.helper.routing.builder'),
            service('emsch.helper.templating.builder'),
            service('emsch.helper.translation.builder'),
        ]);

    $services->set('emsch.helper.routing.builder', RoutingBuilder::class)
        ->parent('emsch.helper.builder');

    $services->set('emsch.helper.templating.builder', TemplateBuilder::class)
        ->parent('emsch.helper.builder');

    $services->set('emsch.helper.translation.builder', TranslationBuilder::class)
        ->parent('emsch.helper.builder');
};
