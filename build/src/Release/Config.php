<?php

declare(strict_types=1);

namespace Build\Release;

class Config
{
    public const REMOTE = 'git@github.com:ems-project/elasticms.git';

    /** @var array<string, string[]> */
    public const REPOSITORIES = [
        'applications' => self::APPLICATIONS,
        'docker' => self::DOCKER,
        'packages' => self::PACKAGES,
    ];

    /** @var string[] */
    public const APPLICATIONS = [
        'elasticms-admin',
        'elasticms-web',
        'elasticms-cli',
        'elasticms-demo',
    ];

    /** @var string[] */
    public const DOCKER = [
        'elasticms-admin-docker',
        'elasticms-web-docker',
        'elasticms-cli-docker',
    ];

    /** @var string[] */
    public const PACKAGES = [
        'EMSAdminUIBundle',
        'EMSClientHelperBundle',
        'EMSCommonBundle',
        'EMSCoreBundle',
        'EMSFormBundle',
        'EMSSubmissionBundle',
        'helpers',
        'xliff',
    ];

    /** @var array<string, string> */
    public const COMPOSER_PACKAGES = [
        'EMSAdminUIBundle' => 'elasticms/admin-ui-bundle',
        'EMSClientHelperBundle' => 'elasticms/client-helper-bundle',
        'EMSCommonBundle' => 'elasticms/common-bundle',
        'EMSCoreBundle' => 'elasticms/core-bundle',
        'EMSFormBundle' => 'elasticms/form-bundle',
        'EMSSubmissionBundle' => 'elasticms/submission-bundle',
        'helpers' => 'elasticms/helpers',
        'xliff' => 'elasticms/xliff',
    ];
}
