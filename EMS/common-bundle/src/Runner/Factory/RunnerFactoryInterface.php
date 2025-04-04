<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Runner\Factory;

use EMS\CommonBundle\Runner\Service\RunnerInterface;

interface RunnerFactoryInterface
{
    public const RUNNER_CONFIG_TYPE = 'type';
    public const RUNNER_CONFIG_TAG = 'tag';

    public function getRunnerType(): string;

    /**
     * @param mixed[] $runnerConfig
     */
    public function createRunner(array $runnerConfig): RunnerInterface;
}
