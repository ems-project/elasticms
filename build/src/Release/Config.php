<?php

declare(strict_types=1);

namespace Build\Release;

class Config
{
    public const string REMOTE = 'https://github.com/ems-project/elasticms.git';
    public const string REMOTE_SSH = 'git@github.com:ems-project/elasticms.git';

    /** @var string[] */
    public const array APPLICATIONS = [
        'elasticms-admin',
        'elasticms-web',
        'elasticms-cli',
        'elasticms-demo',
    ];

    /** @var string[] */
    public const array PACKAGES = [
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
    public const array COMPOSER_PACKAGES = [
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
