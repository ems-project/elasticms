<?php

declare(strict_types=1);

namespace EMS\Release;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;

final class Kernel extends Application
{
    protected string $env = 'prod';
    protected bool $debug = false;

    /**
     * @param Command[] $commands
     */
    public function __construct(
        iterable $commands = [],
        string $name = 'UNKNOWN',
        string $version = 'UNKNOWN',
        string $env = 'dev',
        bool $debug = true
    ) {
        foreach ($commands as $command) {
            $this->add($command);
        }

        $this->debug = $debug;
        $this->env = $env;

        parent::__construct($name, $version);
    }

    public function getEnv(): string
    {
        return $this->env;
    }

    public function getDebug(): bool
    {
        return $this->debug;
    }
}
