<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Command\Admin;

use EMS\CommonBundle\Commands;
use EMS\CommonBundle\Common\Admin\AdminHelper;
use EMS\CommonBundle\Common\Command\AbstractCommand;
use EMS\CommonBundle\Common\Job\JobManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: Commands::ADMIN_NEXT_JOB,
    description: 'Contact the admin to check if a job is planned for the given tag and run it locally.',
    hidden: false
)]
class NextJobCommand extends AbstractCommand
{
    final public const string TAG_ARGUMENT = 'tag';
    final public const string SILENT_OPTION = 'silent';
    private string $tag;
    private bool $silent;

    public function __construct(private readonly AdminHelper $adminHelper, private readonly JobManager $jobManager)
    {
        parent::__construct();
    }

    #[\Override]
    protected function configure(): void
    {
        parent::configure();
        $this->addArgument(self::TAG_ARGUMENT, InputArgument::REQUIRED, 'Tag that identifies the scheduled jobs');
        $this->addOption(self::SILENT_OPTION, null, InputOption::VALUE_NONE, 'Dont echo outputs in the console (only in the admin job logs)');
    }

    #[\Override]
    public function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
        $this->adminHelper->setLogger(new ConsoleLogger($output));
        $this->tag = $this->getArgumentString(self::TAG_ARGUMENT);
        $this->silent = $this->getOptionBool(self::SILENT_OPTION);
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $job = $this->adminHelper->getCoreApi()->admin()->getNextJob($this->tag);
        if (null === $job) {
            $this->io->write('Nothing to execute locally');

            return self::EXECUTE_SUCCESS;
        }

        if (!$this->silent) {
            $this->io->title(\sprintf('Starting job %s', $job->getJobId()));
            $this->io->write(\sprintf('Command: %s', $job->getCommand()));
        }
        $this->jobManager->run($job, $this->silent ? null : $output);

        return self::EXECUTE_SUCCESS;
    }
}
