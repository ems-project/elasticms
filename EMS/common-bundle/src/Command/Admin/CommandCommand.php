<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Command\Admin;

use EMS\CommonBundle\Commands;
use EMS\CommonBundle\Common\Admin\AdminHelper;
use EMS\CommonBundle\Common\Command\AbstractCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: Commands::ADMIN_COMMAND,
    hidden: false
)]
class CommandCommand extends AbstractCommand
{
    final public const string COMMAND = 'remote-command';
    private string $command;

    public function __construct(private readonly AdminHelper $adminHelper)
    {
        parent::__construct();
    }

    #[\Override]
    public function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
        $this->adminHelper->setLogger(new ConsoleLogger($output));
        $this->command = $this->getArgumentString(self::COMMAND);
    }

    #[\Override]
    protected function configure(): void
    {
        parent::configure();
        $this->addArgument(self::COMMAND, InputArgument::REQUIRED, 'Command to remote execute');
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->adminHelper->getCoreApi()->admin()->runCommand($this->command, $this->output);

        return self::EXECUTE_SUCCESS;
    }
}
