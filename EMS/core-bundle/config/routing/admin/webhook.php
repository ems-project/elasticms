<?php

declare(strict_types=1);

use EMS\CoreBundle\Controller\Webhook\WebhookController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return function (RoutingConfigurator $routes) {
    $routes->add('emsco_webhook_subscription_index', '/')
        ->controller([WebhookController::class, 'index'])
        ->methods(['GET', 'POST']);

    $routes->add('emsco_webhook_subscription_delete', '/{webhookSubscription}/delete')
        ->controller([WebhookController::class, 'delete'])
        ->methods(['POST']);
};
