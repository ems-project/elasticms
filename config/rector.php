<?php

declare(strict_types=1);

use Rector\Caching\ValueObject\Storage\FileCacheStorage;
use Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector;
use Rector\Config\RectorConfig;
use Rector\Php81\Rector\Property\ReadOnlyPropertyRector;
use Rector\Symfony\Set\SymfonySetList;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/../EMS',
        __DIR__ . '/../elasticms-admin',
        __DIR__ . '/../elasticms-cli',
        __DIR__ . '/../elasticms-web',
    ])
    ->withCache(
        cacheDirectory: __DIR__ .'/../.cache/rector',
        cacheClass: FileCacheStorage::class
    )
    ->withRules([
        InlineConstructorDefaultToPropertyRector::class,
    ])
    ->withImportNames(importShortClasses: false)
    ->withPhpSets()
    ->withComposerBased(
        twig: true,
        doctrine: true,
        phpunit: true,
        symfony: true
    )
    ->withSets([
        SymfonySetList::SYMFONY_CODE_QUALITY,
        SymfonySetList::SYMFONY_CONSTRUCTOR_INJECTION,
    ])
    ->withSkip([
        __DIR__ . '/../*/config/bundles.php',
        __DIR__ . '/../*/public/*',
        __DIR__ . '/../*/var/*',
        ReadOnlyPropertyRector::class => [
            __DIR__ . '/../EMS/core-bundle/src/Entity',
            __DIR__ . '/../EMS/common-bundle/src/Entity',
            __DIR__ . '/../EMS/submission-bundle/src/Entity',
        ],
    ]);
