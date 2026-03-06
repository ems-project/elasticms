<?php

declare(strict_types=1);

use EMS\ClientHelperBundle\Controller\InlineEditController;
use EMS\ClientHelperBundle\Routes;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return function (RoutingConfigurator $routes): void {
    $routes->add(Routes::INLINE_EDIT_API_RENDER, '/editor/api/render')
        ->controller([InlineEditController::class, 'apiRender'])
        ->methods(['POST']);

    $routes->add(Routes::INLINE_EDIT_API_DRAFT, '/editor/api/draft')
        ->controller([InlineEditController::class, 'apiDraft'])
        ->methods(['POST']);

    $routes->add(Routes::INLINE_EDIT_EDITOR, '/editor{path}')
        ->controller([InlineEditController::class, 'editor'])
        ->defaults(['path' => null])
        ->requirements(['path' => '.*'])
        ->methods(['GET']);
};
