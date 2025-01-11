<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Command;

use EMS\CommonBundle\Commands;
use EMS\CommonBundle\Common\Command\AbstractCommand;
use EMS\CommonBundle\Repository\LogRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: Commands::CLEAR_LOGS,
    description: 'Clear doctrine logs.',
    hidden: false
)]
class ClearLogsCommand extends AbstractCommand
{
    private \DateTime $before;
    /** @var string[] */
    private array $channels = [];

    private const string OPTION_BEFORE = 'before';
    private const string OPTION_CHANNEL = 'channel';

    public function __construct(private readonly LogRepository $logRepository)
    {
        parent::__construct();
    }

    #[\Override]
    protected function configure(): void
    {
        $this
            ->addOption(self::OPTION_BEFORE, null, InputOption::VALUE_OPTIONAL, 'CLear logs older than the strtotime (-1day, -5min, now)', '-1week')
            ->addOption(self::OPTION_CHANNEL, null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Define channels default [app]', ['app'])
        ;
    }

    #[\Override]
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);

        $beforeOption = $this->getOptionString(self::OPTION_BEFORE);
        if (($beforeTime = \strtotime($beforeOption)) === false) {
            throw new \RuntimeException('invalid time');
        }

        $this->before = (new \DateTime())->setTimestamp($beforeTime);
        $this->channels = $this->getOptionStringArray(self::OPTION_CHANNEL, false);
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $logsDeleted = $this->logRepository->clearLogs($this->before, $this->channels);

            $channels = \implode(', ', $this->channels);
            $before = $this->before->format(\DateTimeInterface::ATOM);
            $message = \sprintf('Deleted %d logs before %s for channels: %s', $logsDeleted, $before, $channels);

            ($logsDeleted > 0) ? $this->io->success($message) : $this->io->warning($message);

            return self::EXECUTE_SUCCESS;
        } catch (\Throwable $e) {
            $this->io->error($e->getMessage());

            return self::EXECUTE_ERROR;
        }
    }
}
