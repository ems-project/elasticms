<?php

declare(strict_types=1);

use EMS\CoreBundle\Controller\Admin\UserController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return function (RoutingConfigurator $routes): void {
    $routes->add('emsco_admin_user_reset_password', '/reset-password')
        ->controller([UserController::class, 'resetPasswordUrl'])
        ->methods(['POST']);
};
