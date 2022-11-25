<?php

declare(strict_types=1);

use Symplify\MonorepoBuilder\Config\MBConfig;


return static function (MBConfig $config): void {

    $config->packageDirectories([
        __DIR__ . '/EMS',
        __DIR__ . '/elasticms-cli',
        __DIR__ . '/elasticms-web',
        __DIR__ . '/elasticms-admin',
    ]);

    $config->workers([
        \Symplify\MonorepoBuilder\Release\ReleaseWorker\TagVersionReleaseWorker::class
    ]);

//    $containerConfigurator->import(__DIR__ . '/release/config/services.php');
//
//    $parameters = $containerConfigurator->parameters();
//    $parameters->set(Option::PACKAGE_DIRECTORIES, [
//        __DIR__ . '/EMS',
//        __DIR__ . '/elasticms-cli',
//        __DIR__ . '/elasticms-web',
//        __DIR__ . '/elasticms-admin',
//    ]);
//    $parameters->set('enable_default_release_workers', false);
//    $parameters->set('is_stage_required', true);
};
