<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Command;

use EMS\CommonBundle\Commands;
use EMS\CommonBundle\Common\Command\AbstractCommand;
use EMS\CommonBundle\Service\ElasticaService;
use EMS\CommonBundle\Storage\StorageManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: Commands::STATUS,
    description: 'Returns the health status of the elasticsearch cluster and of the different storage services.',
    hidden: false
)]
class StatusCommand extends AbstractCommand
{
    private const string OPTION_TIMEOUT = 'timeout';
    private const string OPTION_SILENT = 'silent';
    private const string OPTION_WAIT_FOR_STATUS = 'wait-for-status';

    public function __construct(private readonly ElasticaService $elasticaService, private readonly StorageManager $storageManager)
    {
        parent::__construct();
    }

    #[\Override]
    protected function configure(): void
    {
        $this
            ->addOption(
                self::OPTION_SILENT,
                null,
                InputOption::VALUE_NONE,
                'Shows only warning and error messages'
            )
            ->addOption(
                self::OPTION_WAIT_FOR_STATUS,
                null,
                InputOption::VALUE_REQUIRED,
                'One of green, yellow or red. Will wait (until the timeout provided) until the status of the cluster changes to the one provided or better, i.e. green > yellow > red.',
                null
            )
            ->addOption(
                self::OPTION_TIMEOUT,
                null,
                InputOption::VALUE_REQUIRED,
                'Time units. Specifies the period of time to wait for a response. If no response is received before the timeout expires, the request will returns the status red.',
                '10s'
            );
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $silent = $this->getOptionBool(self::OPTION_SILENT);
        if (!$silent) {
            $this->io->section('Start health check');
        }

        $timeout = $this->getOptionString(self::OPTION_TIMEOUT);
        $waitForStatus = $this->getOptionStringNull(self::OPTION_WAIT_FOR_STATUS);

        $status = $this->elasticaService->getHealthStatus($waitForStatus, $timeout);
        $returnCode = 0;
        switch ($status) {
            case 'green':
                if (!$silent) {
                    $this->io->success('Cluster is healthy (green)');
                }
                break;
            case 'yellow':
                --$returnCode;
                $this->io->warning('Replicas shard are not allocated (yellow)');
                break;
            default:
                $returnCode -= 2;
                $this->io->error('The cluster is not healthy (red)');
        }

        $healthyStorages = 0;
        $unhealthyStorages = 0;
        foreach ($this->storageManager->getHealthStatuses() as $name => $status) {
            if ($status) {
                if (!$silent) {
                    $this->io->success(\sprintf('Storage service %s is healthy', $name));
                }
                ++$healthyStorages;
            } else {
                $this->io->warning(\sprintf('Storage service %s is not healthy', $name));
                ++$unhealthyStorages;
            }
        }

        if (0 === $unhealthyStorages && 0 === $healthyStorages) {
            $this->io->warning('There is no storage services defined');
        } elseif (0 === $healthyStorages) {
            $this->io->error('All storage services are failing');
            $returnCode -= 2;
        }

        return $returnCode;
    }
}
