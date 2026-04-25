<?php

declare(strict_types=1);

use EMS\FormBundle\Controller\ConfirmationController;
use EMS\FormBundle\Controller\DebugController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return function (RoutingConfigurator $routes): void {
    $routes->add('emsf_debug_iframe', '/debug/iframe/{ouuid}/{_locale}')
        ->controller([DebugController::class, 'iframe'])
        ->methods(['GET'])
        ->defaults([
            '_locale' => '%locale%',
        ]);

    $routes->add('emsf_debug_form', '/debug/form/{ouuid}/{_locale}')
        ->controller([DebugController::class, 'form'])
        ->methods(['GET', 'POST'])
        ->defaults([
            '_locale' => '%locale%',
        ]);

    $routes->add('emsf_debug_dynamic_field_ajax', '/debug/ajax/{ouuid}/{_locale}')
        ->controller([DebugController::class, 'dynamicFieldAjax'])
        ->methods(['POST'])
        ->defaults([
            '_locale' => '%locale%',
        ]);

    $routes->add('emsf_debug_send_confirmation', '/debug/send-confirmation/{ouuid}/{_locale}')
        ->controller([ConfirmationController::class, 'postDebug'])
        ->methods(['POST'])
        ->defaults([
            '_locale' => '%locale%',
        ]);
};
