<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EMS\CommonBundle\Common\Log\DoctrineHandler;
use EMS\CommonBundle\Common\Log\LocalizedLoggerFactory;
use EMS\CommonBundle\Repository\LogRepository;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->defaults()
        ->private()
        ->autowire(false)
        ->autoconfigure(false);

    $services->set('ems_common.repository.log', LogRepository::class)
        ->lazy()
        ->public()
        ->args([service('doctrine')]);

    $services->set('ems_common.common_log.localized_logger_factory', LocalizedLoggerFactory::class)
        ->args([service('translator')]);

    $services->set('ems_common.monolog.doctrine', DoctrineHandler::class)
        ->args([
            service('ems_common.repository.log'),
            service('security.token_storage'),
            '%ems_common.log_level%',
        ]);
};
