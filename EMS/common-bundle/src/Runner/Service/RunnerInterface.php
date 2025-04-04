<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Runner\Service;

use EMS\CommonBundle\Runner\RunnerStatus;

interface RunnerInterface
{
    public function getTag(): string;

    /**
     * @param string[] $command
     */
    public function start(array $command): string;

    public function status(string $id): RunnerStatus;

    public function output(string $id): string;
}
