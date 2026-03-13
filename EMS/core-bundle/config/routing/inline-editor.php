<?php

declare(strict_types=1);

use EMS\CoreBundle\Controller\InlineEditorController;
use EMS\CoreBundle\Routes;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return function (RoutingConfigurator $routes): void {
    $routes->add(Routes::INLINE_EDIT_API_INIT, '/inline-edit/api/init')
        ->controller([InlineEditorController::class, 'apiInit'])
        ->methods(['POST']);

    $routes->add(Routes::INLINE_EDIT_API_EDIT, '/inline-edit/api/edit')
        ->controller([InlineEditorController::class, 'apiEdit'])
        ->methods(['POST']);

    $routes->add(Routes::INLINE_EDIT_API_DISCARD, '/inline-edit/api/discard/{draftId}')
        ->controller([InlineEditorController::class, 'apiDiscard'])
        ->methods(['DELETE'])
        ->requirements(['draftId' => '\d+']);

    $routes->add(Routes::INLINE_EDIT_API_AUTO_SAVE, '/inline-edit/api/save/{draftId}')
        ->controller([InlineEditorController::class, 'apiSave'])
        ->methods(['POST'])
        ->requirements(['draftId' => '\d+']);

    $routes->add(Routes::INLINE_EDIT_EDITOR, '/inline-edit/{channel}{path}')
        ->controller([InlineEditorController::class, 'editor'])
        ->methods(['GET'])
        ->defaults(['path' => null])
        ->requirements(['path' => '.*', 'channel' => '[a-zA-Z0-9_-]+']);
};
