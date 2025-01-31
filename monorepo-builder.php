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
};
