<?php

declare(strict_types=1);

namespace EMS\Release;

class Config
{
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

    /** @var array<string, string> */
    public const PULL_REQUESTS = [
        'feat' => 'Features',
        'fix' => 'Bug Fixes',
        'docs' => 'Documentation',
        'style' => 'Styles',
        'refactor' => 'Code Refactoring',
        'perf' => 'Performance Improvements',
        'test' => 'Tests',
        'build' => 'Builds',
        'ci' => 'Continuous Integrations',
        'chore' => 'Chores',
        'revert' => 'Reverts',
    ];
}
