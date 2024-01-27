<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector;
use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Symfony\Set\SymfonySetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/EMS/admin-ui-bundle/src',
        __DIR__ . '/EMS/client-helper-bundle/src',
        __DIR__ . '/EMS/client-helper-bundle/tests',
        __DIR__ . '/EMS/common-bundle/src',
        __DIR__ . '/EMS/common-bundle/tests',
        __DIR__ . '/EMS/core-bundle/src',
        __DIR__ . '/EMS/core-bundle/tests',
        __DIR__ . '/EMS/form-bundle/src',
        __DIR__ . '/EMS/form-bundle/tests',
        __DIR__ . '/EMS/helpers/src',
        __DIR__ . '/EMS/helpers/tests',
        __DIR__ . '/EMS/submission-bundle/src',
        __DIR__ . '/EMS/submission-bundle/tests',
        __DIR__ . '/EMS/xliff/src',
        __DIR__ . '/EMS/xliff/tests',
        __DIR__ . '/elasticms-admin/src',
        __DIR__ . '/elasticms-cli/src',
        __DIR__ . '/elasticms-web/src',
        __DIR__ . '/elasticms-admin/tests',
        __DIR__ . '/elasticms-cli/tests',
        __DIR__ . '/elasticms-web/tests',
    ]);

    $rectorConfig->rule(InlineConstructorDefaultToPropertyRector::class);

    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_81,
        SymfonySetList::SYMFONY_54,
        SymfonySetList::SYMFONY_CODE_QUALITY,
        SymfonySetList::SYMFONY_CONSTRUCTOR_INJECTION,
    ]);

    $rectorConfig->importNames();
    $rectorConfig->importShortClasses(false);
};
