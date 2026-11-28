<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EMS\CommonBundle\Runner\Factory\DockerRemoteFactory;
use EMS\CommonBundle\Runner\Factory\OpenShiftFactory;
use EMS\CommonBundle\Runner\RunnerManager;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->defaults()
        ->private();

    $services->set('ems_common.runner.manager', RunnerManager::class)
        ->args([
            service('logger'),
            tagged_iterator('ems_common.runner.factory'),
            '%ems_common.runners%',
        ]);

    $services->set('ems_common.runner.factory.openshift', OpenShiftFactory::class)
        ->args([
            service('logger'),
            service('ems_common.composer.info'),
        ])
        ->tag('ems_common.runner.factory', ['alias' => 'openshift']);

    $services->set('ems_common.runner.factory.docker-remote', DockerRemoteFactory::class)
        ->args([
            service('logger'),
            service('ems_common.composer.info'),
        ])
        ->tag('ems_common.runner.factory', ['alias' => 'docker-remote']);
};
