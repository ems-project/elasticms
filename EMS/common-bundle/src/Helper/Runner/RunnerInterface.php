<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Helper\Runner;

interface RunnerInterface
{
    /**
     * @param string[] $command
     */
    public function start(array $command): string;
}
