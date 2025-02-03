<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector;
use Rector\Config\RectorConfig;
use Rector\Symfony\Set\SymfonySetList;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/../EMS/admin-ui-bundle/src',
        __DIR__ . '/../EMS/client-helper-bundle/src',
        __DIR__ . '/../EMS/client-helper-bundle/tests',
        __DIR__ . '/../EMS/common-bundle/src',
        __DIR__ . '/../EMS/common-bundle/tests',
        __DIR__ . '/../EMS/core-bundle/migrations',
        __DIR__ . '/../EMS/core-bundle/src',
        __DIR__ . '/../EMS/core-bundle/tests',
        __DIR__ . '/../EMS/form-bundle/src',
        __DIR__ . '/../EMS/form-bundle/tests',
        __DIR__ . '/../EMS/helpers/src',
        __DIR__ . '/../EMS/helpers/tests',
        __DIR__ . '/../EMS/submission-bundle/src',
        __DIR__ . '/../EMS/submission-bundle/tests',
        __DIR__ . '/../EMS/xliff/src',
        __DIR__ . '/../EMS/xliff/tests',
        __DIR__ . '/../elasticms-admin/src',
        __DIR__ . '/../elasticms-admin/tests',
        __DIR__ . '/../elasticms-cli/src',
        __DIR__ . '/../elasticms-cli/migrations',
        __DIR__ . '/../elasticms-cli/tests',
        __DIR__ . '/../elasticms-web/src',
        __DIR__ . '/../elasticms-web/migrations',
        __DIR__ . '/../elasticms-web/tests',
    ])
    ->withRules([
        InlineConstructorDefaultToPropertyRector::class,
    ])
    ->withImportNames()
    ->withImportNames(importShortClasses: false)
    ->withPhpSets()
    ->withSets([
        SymfonySetList::SYMFONY_64,
        SymfonySetList::SYMFONY_CODE_QUALITY,
        SymfonySetList::SYMFONY_CONSTRUCTOR_INJECTION,
    ])
;