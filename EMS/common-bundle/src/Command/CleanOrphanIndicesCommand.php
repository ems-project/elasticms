<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'ems:elasticsearch:clean-orphan-indices',
    description: 'Delete all Elasticsearch indices that are not associated with any alias.'
)]
final class CleanOrphanIndicesCommand extends Command
{
    public function __construct(private readonly HttpClientInterface $httpClient)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('elasticsearch-url', InputArgument::REQUIRED, 'The base URL of the Elasticsearch cluster (e.g. http://localhost:9200)')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Actually delete the orphan indices instead of just listing them (dry-run by default).')
            ->addOption('include-system', null, InputOption::VALUE_NONE, 'Include system indices (starting with ".") in the cleanup.')
            ->addOption('timeout', null, InputOption::VALUE_REQUIRED, 'HTTP timeout in seconds.', '10');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $elasticsearch = \rtrim((string) $input->getArgument('elasticsearch-url'), '/');
        $force = (bool) $input->getOption('force');
        $includeSystem = (bool) $input->getOption('include-system');
        $timeout = (int) $input->getOption('timeout');

        $io->title('Elasticsearch orphan indices cleanup');
        $io->writeln(\sprintf('Cluster URL: <info>%s</info>', $elasticsearch));
        $io->writeln(\sprintf('Mode: <comment>%s</comment>', $force ? 'DELETE' : 'DRY-RUN (no deletion performed)'));
        if (!$includeSystem) {
            $io->writeln('System indices (prefix ".") will be ignored.');
        }
        $io->newLine();

        try {
            // 1) Get list of all indices
            $indicesResp = $this->httpClient->request('GET', $elasticsearch.'/_cat/indices?format=json', [
                'timeout' => $timeout,
            ]);
            $indices = $indicesResp->toArray();

            // 2) Get aliases map
            $aliasesResp = $this->httpClient->request('GET', $elasticsearch.'/_aliases', [
                'timeout' => $timeout,
            ]);
            $aliasesMap = $aliasesResp->toArray(false);
        } catch (TransportExceptionInterface|HttpExceptionInterface $e) {
            $io->error(\sprintf('Error while calling Elasticsearch: %s', $e->getMessage()));

            return Command::FAILURE;
        }

        // Filter indices
        $allIndexNames = [];
        foreach ($indices as $row) {
            if (!isset($row['index'])) {
                continue; // unexpected format
            }
            $name = (string) $row['index'];
            if (!$includeSystem && \str_starts_with($name, '.')) {
                continue; // skip system indices
            }
            $allIndexNames[] = $name;
        }

        // Identify orphan indices
        $orphans = [];
        foreach ($allIndexNames as $name) {
            $hasAliases = isset($aliasesMap[$name]['aliases']) && \count($aliasesMap[$name]['aliases']) > 0;
            if (!$hasAliases) {
                $orphans[] = $name;
            }
        }

        if (empty($orphans)) {
            $io->success('No orphan indices found. The cluster is clean 👍');

            return Command::SUCCESS;
        }

        $io->section(\sprintf('Found %d orphan indices', \count($orphans)));
        $io->listing($orphans);

        if (!$force) {
            $io->success('Dry-run completed. Add --force to actually delete these indices.');

            return Command::SUCCESS;
        }

        // 3) Delete orphan indices
        $io->section('Deleting orphan indices...');
        $deleted = [];
        $failed = [];

        foreach ($orphans as $index) {
            try {
                $resp = $this->httpClient->request('DELETE', $elasticsearch.'/'.\rawurlencode($index), [
                    'timeout' => $timeout,
                ]);
                $status = $resp->getStatusCode();
                if ($status >= 200 && $status < 300) {
                    $deleted[] = $index;
                    $io->writeln(\sprintf('<info>✔</info> Deleted: %s', $index));
                } else {
                    $failed[] = [$index, 'HTTP '.$status];
                    $io->writeln(\sprintf('<error>✘</error> Failed to delete %s (HTTP %d)', $index, $status));
                }
            } catch (TransportExceptionInterface|HttpExceptionInterface $e) {
                $failed[] = [$index, $e->getMessage()];
                $io->writeln(\sprintf('<error>✘</error> Failed to delete %s (%s)', $index, $e->getMessage()));
            }
        }

        $io->newLine();
        if (!empty($deleted)) {
            $io->success(\sprintf('%d indices deleted successfully.', \count($deleted)));
        }
        if (!empty($failed)) {
            $io->warning(\sprintf('%d deletions failed.', \count($failed)));
        }

        return empty($failed) ? Command::SUCCESS : Command::FAILURE;
    }
}
