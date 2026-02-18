<?php

declare(strict_types=1);

use EMS\ClientHelperBundle\Controller\InlineEditController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return function (RoutingConfigurator $routes): void {
    $routes->add('emsch_inline_edit_editor', '/editor/{uri}')
        ->controller([InlineEditController::class, 'editor'])
        ->requirements(['uri' => '.+'])
        ->methods(['GET']);
};
