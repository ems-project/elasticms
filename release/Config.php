<?php

declare(strict_types=1);

namespace EMS\Release;

class Config
{
    public const REPOSITORIES = [
        'applications' => self::APPLICATIONS,
        'docker' => self::DOCKER,
        'packages' => self::PACKAGES,
    ];

    public const APPLICATIONS = [
        'elasticms-admin' => 'elasticms/elasticms-admin',
        'elasticms-web' => 'elasticms/elasticms-web',
        'elasticms-cli' => 'elasticms/elasticms-cli',
    ];

    public const DOCKER = [
        'elasticms-admin-docker' => 'elasticms/elasticms-admin-docker',
        'elasticms-web-docker' => 'elasticms/elasticms-web-docker',
        'elasticms-cli-docker' => 'elasticms/elasticms-cli-docker',
    ];

    public const PACKAGES = [
        'EMSClientHelperBundle' => 'elasticms/client-helper-bundle',
        'EMSCommonBundle' => 'elasticms/common-bundle',
        'EMSCoreBundle' => 'elasticms/core-bundle',
        'EMSFormBundle' => 'elasticms/form-bundle',
        'EMSSubmissionBundle' => 'elasticms/submission-bundle',
        'helpers' => 'elasticms/helpers',
        'xliff' => 'elasticms/xliff'
    ];
}