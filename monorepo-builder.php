<?php

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\ComposerJsonManipulator\ValueObject\ComposerJsonSection;
use Symplify\MonorepoBuilder\ValueObject\Option;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set(Option::PACKAGE_DIRECTORIES, [
        __DIR__ . '/EMS',
        __DIR__ . '/elasticms-cli',
        __DIR__ . '/elasticms-web',
        __DIR__ . '/elasticms-admin',
    ]);

    // how skip packages in loaded direectories?
//    $parameters->set(Option::PACKAGE_DIRECTORIES_EXCLUDES, [__DIR__ . '/packages/secret-package']);

    // "merge" command related

    // what extra parts to add after merge?
//    $parameters->set(Option::DATA_TO_APPEND, [
//        ComposerJsonSection::AUTOLOAD_DEV => [
//            'psr-4' => [
//                'Symplify\Tests\\' => 'tests',
//            ],
//        ],
//        ComposerJsonSection::REQUIRE_DEV => [
//            'phpstan/phpstan' => '^0.12',
//        ],
//    ]);
//
//    $parameters->set(Option::DATA_TO_REMOVE, [
//        ComposerJsonSection::REQUIRE => [
//            // the line is removed by key, so version is irrelevant, thus *
//            'phpunit/phpunit' => '*',
//        ],
//    ]);
};
