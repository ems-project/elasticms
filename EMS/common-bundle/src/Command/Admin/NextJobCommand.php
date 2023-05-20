<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Command\Admin;

use EMS\CommonBundle\Commands;
use EMS\CommonBundle\Common\Admin\AdminHelper;
use EMS\CommonBundle\Common\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

class NextJobCommand extends AbstractCommand
{
    public const TAG_ARGUMENT = 'tag';
    protected static $defaultName = Commands::ADMIN_NEXT_JOB;
    private string $tag;

    public function __construct(private readonly AdminHelper $adminHelper)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        parent::configure();
        $this->addArgument(self::TAG_ARGUMENT, InputArgument::REQUIRED, 'Tag that identifies the command family');
    }

    public function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
        $this->adminHelper->setLogger(new ConsoleLogger($output));
        $this->tag = $this->getArgumentString(self::TAG_ARGUMENT);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $job = $this->adminHelper->getCoreApi()->admin()->startNextJob($this->tag);
        if (null === $job) {
            $this->io->write('Nothing to execute locally');

            return self::EXECUTE_SUCCESS;
        }

        return self::EXECUTE_SUCCESS;
    }
}
