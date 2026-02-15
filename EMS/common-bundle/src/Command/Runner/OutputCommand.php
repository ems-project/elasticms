<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Command\Runner;

use EMS\CommonBundle\Commands;
use EMS\CommonBundle\Common\Command\AbstractCommand;
use EMS\CommonBundle\Runner\RunnerManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: Commands::RUNNER_OUTPUT,
    description: 'Get the current output of a remote runner.',
    hidden: false
)]
class OutputCommand extends AbstractCommand
{
    public const ARGUMENT_TAG = 'tag';
    public const ARGUMENT_ID = 'id';
    private string $tag;
    private string $id;

    public function __construct(private readonly RunnerManager $runnerManager)
    {
        parent::__construct();
    }

    #[\Override]
    protected function configure(): void
    {
        $this
            ->addArgument(self::ARGUMENT_TAG, InputArgument::REQUIRED, "Runner's tag")
            ->addArgument(self::ARGUMENT_ID, InputArgument::REQUIRED, 'Runner identifier')
        ;
    }

    #[\Override]
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
        $this->tag = $this->getArgumentString(self::ARGUMENT_TAG);
        $this->id = $this->getArgumentString(self::ARGUMENT_ID);
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output = $this->runnerManager->output($this->tag, $this->id);
        $this->io->write($output, true);

        return self::EXECUTE_SUCCESS;
    }
}
