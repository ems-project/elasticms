<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Runner\Service;

interface RunnerInterface
{
    public function getTag(): string;

    /**
     * @param string[] $command
     */
    public function start(array $command): string;
}
