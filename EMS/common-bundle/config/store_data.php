<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EMS\CommonBundle\Common\StoreData\Factory\StoreDataCacheFactory;
use EMS\CommonBundle\Common\StoreData\Factory\StoreDataEntityFactory;
use EMS\CommonBundle\Common\StoreData\Factory\StoreDataFileSystemFactory;
use EMS\CommonBundle\Common\StoreData\Factory\StoreDataS3Factory;
use EMS\CommonBundle\Common\StoreData\StoreDataManager;
use EMS\CommonBundle\Repository\StoreDataRepository;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->defaults()
        ->private();

    $services->set('ems_common.repository.store_data', StoreDataRepository::class)
        ->public()
        ->args([service('doctrine')]);

    $services->set('ems_common.store_data.manager', StoreDataManager::class)
        ->args([
            service('logger'),
            tagged_iterator('ems_common.store_data.factory'),
            '%ems_common.store_data_services%',
        ]);

    $services->set('ems_common.store_data.factory.db', StoreDataEntityFactory::class)
        ->args([service('ems_common.repository.store_data')])
        ->tag('ems_common.store_data.factory', ['alias' => 'db']);

    $services->set('ems_common.store_data.factory.cache', StoreDataCacheFactory::class)
        ->args([service('ems.common.cache')])
        ->tag('ems_common.store_data.factory', ['alias' => 'cache']);

    $services->set('ems_common.store_data.factory.fs', StoreDataFileSystemFactory::class)
        ->tag('ems_common.store_data.factory', ['alias' => 'fs']);

    $services->set('ems_common.store_data.factory.s3', StoreDataS3Factory::class)
        ->tag('ems_common.store_data.factory', ['alias' => 's3']);
};
