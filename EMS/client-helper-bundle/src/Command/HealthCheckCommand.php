<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Command;

use EMS\ClientHelperBundle\Commands;
use EMS\ClientHelperBundle\Exception\ClusterHealthNotGreenException;
use EMS\ClientHelperBundle\Exception\ClusterHealthRedException;
use EMS\ClientHelperBundle\Exception\IndexNotFoundException;
use EMS\ClientHelperBundle\Helper\Environment\EnvironmentHelper;
use EMS\CommonBundle\Common\Command\AbstractCommand;
use EMS\CommonBundle\Service\ElasticaService;
use EMS\CommonBundle\Storage\StorageManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: Commands::HEALTH_CHECK,
    description: 'Performs system health check.',
    hidden: false
)]
final class HealthCheckCommand extends AbstractCommand
{
    public function __construct(
        private readonly EnvironmentHelper $environmentHelper,
        private readonly ElasticaService $elasticaService,
        private readonly ?StorageManager $storageManager = null,
    ) {
        parent::__construct();
    }

    #[\Override]
    protected function configure(): void
    {
        $this
            ->setHelp('Verify that the assets folder exists and is not empty. Verify that the Elasticsearch cluster is at least yellow and that the configured indexes exist.')
            ->addOption('green', 'g', InputOption::VALUE_NONE, 'Require a green Elasticsearch cluster health.', null)
            ->addOption('skip-storage', 's', InputOption::VALUE_NONE, 'Skip the storage health check.', null);
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title('Performing Health Check');

        $this->checkElasticSearch($this->getOptionBool('green'));
        $this->checkIndexes();
        $this->checkStorage($this->getOptionBool('skip-storage'));

        $this->io->success('Health check finished.');

        return self::EXECUTE_SUCCESS;
    }

    private function checkElasticSearch(bool $green): void
    {
        $this->io->section('Elasticsearch');
        $status = $this->elasticaService->getHealthStatus();

        if ('red' === $status) {
            $this->io->error('Cluster health is RED');
            throw new ClusterHealthRedException();
        }

        if ($green && 'green' !== $status) {
            $this->io->error('Cluster health is NOT GREEN');
            throw new ClusterHealthNotGreenException();
        }

        $this->io->success('Elasticsearch is working.');
    }

    private function checkIndexes(): void
    {
        $this->io->section('Indexes');
        $countAliases = 0;
        $countIndices = 0;
        foreach ($this->environmentHelper->getEnvironments() as $environment) {
            ++$countAliases;
            try {
                $countIndices += \count($this->elasticaService->getIndicesFromAlias($environment->getAlias()));
            } catch (\Throwable $e) {
                $this->io->error(\sprintf('Alias %s not found with error: %s', $environment->getAlias(), $e->getMessage()));
                throw new IndexNotFoundException();
            }
        }

        $this->io->success(\sprintf('%d indices have been found in %d aliases.', $countIndices, $countAliases));
    }

    private function checkStorage(bool $skip): void
    {
        $this->io->section('Storage');

        if ($skip) {
            $this->io->note('Skipping Storage Health Check.');

            return;
        }

        if (null === $this->storageManager) {
            $this->io->warning('Skipping assets because health check has no access to a storageManager, enable storage ?');

            return;
        }

        $adapters = [];

        foreach ($this->storageManager->getHealthStatuses() as $name => $status) {
            $adapters[] = $name.' -> '.($status ? 'green' : 'red');
        }

        $this->io->listing($adapters);
    }
}
