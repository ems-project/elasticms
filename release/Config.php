<?php

declare(strict_types=1);

namespace EMS\Release;

class Config
{
    public static string $organization = 'ems-project';

    public static array $applications = [
        'admin' => 'elasticms/elasticms-admin',
        'web' => 'elasticms/elasticms-web',
        'cli' => 'elasticms/elasticms-cli',
    ];

    public static array $packages = [
        'EMSClientHelperBundle' => 'elasticms/client-helper-bundle',
        'EMSCommonBundle' => 'elasticms/common-bundle',
        'EMSCoreBundle' => 'elasticms/core-bundle',
        'EMSFormBundle' => 'elasticms/form-bundle',
        'EMSSubmissionBundle' => 'elasticms/submission-bundle',
        'helpers' => 'elasticms/helpers',
        'xliff' => 'elasticms/xliff'
    ];
}