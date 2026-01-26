<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EMS\CommonBundle\Contracts\Log\LocalizedLoggerInterface;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->defaults()
        ->private()
        ->autowire(false)
        ->autoconfigure(false);

    $services->set('emsco.logger', LocalizedLoggerInterface::class)
        ->args([
            service('logger'),
            'ems_logger',
        ])

        ->tag('monolog.logger', ['channel' => 'core']);

    $services->set('emsco.logger.audit', LocalizedLoggerInterface::class)
        ->args([
            service('logger'),
            'ems_logger',
        ])

        ->tag('monolog.logger', ['channel' => 'audit']);
};
