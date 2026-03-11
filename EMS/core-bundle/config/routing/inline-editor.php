<?php

declare(strict_types=1);

use EMS\CoreBundle\Controller\InlineEditorController;
use EMS\CoreBundle\Routes;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return function (RoutingConfigurator $routes): void {
    $routes->add(Routes::INLINE_EDIT_API_RENDER, '/inline-edit/api/init')
        ->controller([InlineEditorController::class, 'apiInit'])
        ->methods(['POST']);

    $routes->add(Routes::INLINE_EDIT_API_DRAFT, '/inline-edit/api/edit')
        ->controller([InlineEditorController::class, 'apiEdit'])
        ->methods(['POST']);

    $routes->add(Routes::INLINE_EDIT_EDITOR, '/inline-edit/{channel}{path}')
        ->controller([InlineEditorController::class, 'editor'])
        ->defaults(['path' => null])
        ->requirements(['path' => '.*', 'channel' => '[a-zA-Z0-9_-]+'])
        ->methods(['GET']);
};
