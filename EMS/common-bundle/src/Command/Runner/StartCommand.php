<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Command\Runner;

use EMS\CommonBundle\Commands;
use EMS\CommonBundle\Common\Command\AbstractCommand;
use EMS\CommonBundle\Helper\Runner\OpenShiftJob;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: Commands::RUNNER_START,
    description: 'Start a command in a remote runner.',
    hidden: false
)]
class StartCommand extends AbstractCommand
{
    public const ARGUMENT_TAG = 'tag';
    public const ARGUMENT_COMMAND = 'runner_command';
    private string $tag;
    /**
     * @var string[]
     */
    private array $command;

    #[\Override]
    protected function configure(): void
    {
        $this
            ->addArgument(self::ARGUMENT_TAG, InputArgument::REQUIRED, 'Runner\'s tag')
            ->addArgument(self::ARGUMENT_COMMAND, InputArgument::IS_ARRAY, 'Command to run')
        ;
    }

    #[\Override]
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
        $this->tag = $this->getArgumentString(self::ARGUMENT_TAG);
        $this->command = $this->getArgumentStringArray(self::ARGUMENT_COMMAND);
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $runner = new OpenShiftJob(
            'https://api.ssb-02.paas.cloud.ssbdc.be:6443/apis/batch/v1/namespaces/webagency-bu-inami-services/',
            'sha256~E2MROs10-X9NP-WWAToQNdXe9CEZT2dOtrD7RYM7uEY',
        );
        $runner->start($this->command);

        return self::EXECUTE_SUCCESS;
    }
}
