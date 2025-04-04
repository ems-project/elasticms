<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Runner\Factory;

use EMS\CommonBundle\Runner\Service\RunnerInterface;

interface RunnerFactoryInterface
{
    public const string RUNNER_CONFIG_TYPE = 'type';
    public const string RUNNER_CONFIG_TAG = 'tag';
    final public const string RUNNER_OPENSHIFT_EMS_VERSION_REPLACER = '%ems_version%';

    public function getRunnerType(): string;

    /**
     * @param mixed[] $runnerConfig
     */
    public function createRunner(array $runnerConfig): RunnerInterface;
}
