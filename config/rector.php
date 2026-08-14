<?php

declare(strict_types=1);

use Rector\Caching\ValueObject\Storage\FileCacheStorage;
use Rector\CodeQuality\Rector\BooleanAnd\RepeatedAndNotEqualToNotInArrayRector;
use Rector\CodeQuality\Rector\BooleanOr\RepeatedOrEqualToInArrayRector;
use Rector\CodeQuality\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector;
use Rector\CodingStyle\Rector\ClassLike\NewlineBetweenClassLikeStmtsRector;
use Rector\CodingStyle\Rector\Stmt\NewlineAfterStatementRector;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\ClassMethod\RemoveUselessParamTagRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUselessReturnTagRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUselessUnionReturnDocblockRector;
use Rector\DeadCode\Rector\Property\RemoveUselessVarTagRector;
use Rector\Php81\Rector\Property\ReadOnlyPropertyRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
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
    ->withImportNames(importShortClasses: false)
    ->withPhpSets()
    ->withComposerBased(
        twig: true,
        doctrine: true,
        phpunit: true,
        symfony: true
    )
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        codingStyle: true
    )
    ->withSets([
        SymfonySetList::SYMFONY_CODE_QUALITY,
        SymfonySetList::SYMFONY_CONSTRUCTOR_INJECTION,
    ])
    ->withSkip([
        // Paths
        __DIR__ . '/../*/config/bundles.php',
        __DIR__ . '/../*/public/*',
        __DIR__ . '/../*/var/*',
        __DIR__ . '/../*/migrations/*',
        __DIR__ . '/../EMS/*/assets/*',
        __DIR__ . '/../EMS/*/migrations/*',
        __DIR__ . '/../EMS/*/public/*',
        __DIR__ . '/../EMS/*/translations/*',
        // Rectors
        FlipTypeControlToUseExclusiveTypeRector::class,
        NewlineAfterStatementRector::class,
        NewlineBetweenClassLikeStmtsRector::class,
        ReadOnlyPropertyRector::class => [
            __DIR__ . '/../EMS/common-bundle/src/Entity',
            __DIR__ . '/../EMS/core-bundle/src/Entity',
            __DIR__ . '/../EMS/submission-bundle/src/Entity',
        ],
        RemoveUselessParamTagRector::class,
        RemoveUselessReturnTagRector::class,
        RemoveUselessUnionReturnDocblockRector::class,
        RemoveUselessVarTagRector::class,
        RenameMethodRector::class,
        RepeatedAndNotEqualToNotInArrayRector::class,
        RepeatedOrEqualToInArrayRector::class,
    ]);
