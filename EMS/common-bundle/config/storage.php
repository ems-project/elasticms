<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EMS\CommonBundle\Controller\FileController;
use EMS\CommonBundle\Storage\Factory\ApiFactory;
use EMS\CommonBundle\Storage\Factory\EntityFactory;
use EMS\CommonBundle\Storage\Factory\FileSystemFactory;
use EMS\CommonBundle\Storage\Factory\HttpFactory;
use EMS\CommonBundle\Storage\Factory\S3Factory;
use EMS\CommonBundle\Storage\Factory\SftpFactory;
use EMS\CommonBundle\Storage\Processor\Processor;
use EMS\CommonBundle\Storage\StorageManager;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->defaults()
        ->private();

    $services->set('ems_common.storage.manager', StorageManager::class)
        ->args([
            service('logger'),
            service('file_locator'),
            service('ems.common.cache'),
            tagged_iterator('ems_common.storage.factory'),
            '%ems_common.hash_algo%',
            '%ems_common.storages%',
        ]);

    $services->set(FileController::class)
        ->args([
            service('ems_common.storage.processor'),
        ])
        ->call('setContainer')
        ->tag('controller.service_arguments')
        ->tag('container.service_subscriber');

    $services->set('ems_common.storage.processor', Processor::class)
        ->args([
            service('ems_common.storage.manager'),
            service('logger'),
            service('ems_common.helper.cache'),
            service('file_locator'),
        ])
        ->tag('monolog.logger', ['channel' => 'ems_common']);

    $services->set('ems_common.storage.factory.fs', FileSystemFactory::class)
        ->args([
            service('logger'),
            '%kernel.project_dir%',
        ])
        ->tag('ems_common.storage.factory', ['alias' => 'fs']);

    $services->set('ems_common.storage.factory.http', HttpFactory::class)
        ->args([service('logger')])
        ->tag('ems_common.storage.factory', ['alias' => 'http']);

    $services->set('ems_common.storage.factory.db', EntityFactory::class)
        ->args([
            service('logger'),
            service('doctrine'),
        ])
        ->tag('ems_common.storage.factory', ['alias' => 'db']);

    $services->set('ems_common.storage.factory.s3', S3Factory::class)
        ->args([
            service('logger'),
            service('ems.common.cache'),
        ])
        ->tag('ems_common.storage.factory', ['alias' => 's3']);

    $services->set('ems_common.storage.factory.sftp', SftpFactory::class)
        ->args([service('logger')])
        ->tag('ems_common.storage.factory', ['alias' => 'sftp']);

    $services->set('ems_common.storage.factory.api', ApiFactory::class)
        ->args([
            service('logger'),
            service('ems_common.core_api.token_store'),
        ])
        ->tag('ems_common.storage.factory', ['alias' => 'api']);

    $services->alias(StorageManager::class, 'ems_common.storage.manager');

    $services->alias(Processor::class, 'ems_common.storage.processor');
};
