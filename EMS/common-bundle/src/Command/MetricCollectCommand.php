<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Command;

use EMS\CommonBundle\Common\Command\AbstractCommand;
use EMS\CommonBundle\Common\Metric\MetricCollector;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class MetricCollectCommand extends AbstractCommand
{
    private const string OPTION_CLEAR = 'clear';

    public function __construct(private readonly MetricCollector $metricCollector)
    {
        parent::__construct();
    }

    #[\Override]
    protected function configure(): void
    {
        $this
            ->addOption(self::OPTION_CLEAR, null, InputOption::VALUE_NONE, 'clear metrics before collecting')
        ;
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title('EMS - Metric - Collect');

        if ($this->getOptionBool(self::OPTION_CLEAR)) {
            $this->metricCollector->clear();
            $this->io->comment('Cleared metrics');
        }

        $this->metricCollector->collect();

        $this->io->success('Collected metrics');

        return self::EXECUTE_SUCCESS;
    }
}
