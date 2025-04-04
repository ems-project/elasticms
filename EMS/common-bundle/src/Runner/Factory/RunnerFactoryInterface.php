<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Runner\Factory;

use EMS\CommonBundle\Runner\Service\RunnerInterface;

interface RunnerFactoryInterface
{
    public function getRunnerType(): string;

    /**
     * @param mixed[] $runnerConfig
     */
    public function createService(array $runnerConfig): ?RunnerInterface;
}
