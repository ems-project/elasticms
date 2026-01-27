<?php

declare(strict_types=1);

use EMS\FormBundle\Controller\ConfirmationController;
use EMS\FormBundle\Controller\FormController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return function (RoutingConfigurator $routes): void {
    $routes->add('emsf_iframe', '/iframe/{ouuid}/{_locale}')
        ->controller([FormController::class, 'iframe'])
        ->methods(['GET'])
        ->defaults([
            '_locale' => '%locale%',
        ]);

    $routes->add('emsf_form', '/form/{ouuid}/{_locale}')
        ->controller([FormController::class, 'submitForm'])
        ->methods(['POST'])
        ->defaults([
            '_locale' => '%locale%',
        ]);

    $routes->add('emsf_init_form', '/init-form/{ouuid}/{_locale}')
        ->controller([FormController::class, 'initForm'])
        ->methods(['POST'])
        ->defaults([
            '_locale' => '%locale%',
        ]);

    $routes->add('emsf_dynamic_field_ajax', '/ajax/{ouuid}/{_locale}')
        ->controller([FormController::class, 'dynamicFieldAjax'])
        ->methods(['POST'])
        ->defaults([
            '_locale' => '%locale%',
        ]);

    $routes->add('emsf_confirmation', '/form/send-confirmation/{ouuid}/{_locale}')
        ->controller([ConfirmationController::class, 'postSend'])
        ->methods(['POST'])
        ->defaults([
            '_locale' => '%locale%',
        ]);
};
