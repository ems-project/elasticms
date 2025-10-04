<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Runner\Factory;

use EMS\CommonBundle\Runner\Service\RunnerInterface;

interface RunnerFactoryInterface
{
    public const string RUNNER_CONFIG_TYPE = 'type';
    public const string RUNNER_CONFIG_TAG = 'tag';
    public const string RUNNER_CONFIG_WORKER_COMMAND = 'worker-command';
    final public const string RUNNER_EMS_VERSION_REPLACER = '%ems_version%';
    final public const string RUNNER_EMS_JOB_ID_REPLACER = '%ems_job_id%';

    public function getRunnerType(): string;

    /**
     * @param mixed[] $runnerConfig
     */
    public function createRunner(array $runnerConfig): RunnerInterface;
}
