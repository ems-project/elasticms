<?php

declare(strict_types=1);

namespace EMS\Release;

use EMS\Release\Command\InfoCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\HelpCommand;
use Symfony\Component\Console\Command\ListCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;

class ReleaseApplication extends Application
{
    public function __construct()
    {
        parent::__construct('EMS release', '1.0.0');
        $this->setDefaultCommand('info');
    }

    protected function getDefaultCommands(): array
    {
        return [
            new InfoCommand(),
            new HelpCommand(),
            new ListCommand(),
        ];
    }

    protected function getDefaultInputDefinition(): InputDefinition
    {
        return new InputDefinition([
            new InputArgument('command', InputArgument::REQUIRED, 'The command to execute'),
            new InputOption('--help', '-h', InputOption::VALUE_NONE, 'Display help for the given command.'),
            new InputOption('--verbose', '-v|vv|vvv', InputOption::VALUE_NONE, 'Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug'),
            new InputOption('--version', '-V', InputOption::VALUE_NONE, 'Display this application version'),
        ]);
    }
}
