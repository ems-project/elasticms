<?php

declare(strict_types=1);

use EMS\ClientHelperBundle\Controller\InlineEditController;
use EMS\ClientHelperBundle\Routes;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return function (RoutingConfigurator $routes): void {
    $routes->add(Routes::INLINE_EDIT_RENDER, '/editor/render')
        ->controller([InlineEditController::class, 'render'])
        ->methods(['POST']);

    $routes->add(Routes::INLINE_EDIT_EDITOR, '/editor{path}')
        ->controller([InlineEditController::class, 'editor'])
        ->requirements(['path' => '.+'])
        ->methods(['GET']);
};
