<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Command;

use EMS\CommonBundle\Common\Command\AbstractCommand;
use EMS\Helpers\Standard\Json;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'ems:elasticsearch:clean-orphan-indices',
    description: 'Delete all Elasticsearch indices that are not associated with any alias.'
)]
final class CleanOrphanIndicesCommand extends AbstractCommand
{
    private const string ARGUMENT_ELASTICSEARCH_URL = 'elasticsearch-url';
    private const string OPTION_FORCE = 'force';
    private const string OPTION_INCLUDE_SYSTEM = 'include-system';
    private const string OPTION_TIMEOUT = 'timeout';
    private const string OPTION_HEADERS = 'headers';
    private string $elasticsearch;
    private bool $force;
    private bool $includeSystem;
    private int $timeout;
    /**
     * @var mixed[]
     */
    private array $headers;

    public function __construct(private readonly HttpClientInterface $httpClient)
    {
        parent::__construct();
    }

    #[\Override]
    protected function configure(): void
    {
        $this
            ->addArgument(self::ARGUMENT_ELASTICSEARCH_URL, InputArgument::REQUIRED, 'The base URL of the Elasticsearch cluster (e.g. http://localhost:9200)')
            ->addOption(self::OPTION_FORCE, null, InputOption::VALUE_NONE, 'Actually delete the orphan indices instead of just listing them (dry-run by default).')
            ->addOption(self::OPTION_INCLUDE_SYSTEM, null, InputOption::VALUE_NONE, 'Include system indices (starting with ".") in the cleanup.')
            ->addOption(self::OPTION_TIMEOUT, null, InputOption::VALUE_REQUIRED, 'HTTP timeout in seconds.', '10')
            ->addOption(self::OPTION_HEADERS, null, InputOption::VALUE_REQUIRED, 'Extra headers for the HTTP client (JSON encoded).', '[]');
    }

    #[\Override]
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
        $this->elasticsearch = \rtrim($this->getArgumentString(self::ARGUMENT_ELASTICSEARCH_URL), '/');
        $this->force = $this->getOptionBool(self::OPTION_FORCE);
        $this->includeSystem = $this->getOptionBool(self::OPTION_INCLUDE_SYSTEM);
        $this->timeout = $this->getOptionInt(self::OPTION_TIMEOUT);
        $this->headers = Json::decode($this->getOptionString(self::OPTION_HEADERS));
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title('Elasticsearch orphan indices cleanup');
        $this->io->writeln(\sprintf('Cluster URL: <info>%s</info>', $this->elasticsearch));
        $this->io->writeln(\sprintf('Mode: <comment>%s</comment>', $this->force ? 'DELETE' : 'DRY-RUN (no deletion performed)'));
        if (!$this->includeSystem) {
            $this->io->writeln('System indices (prefix ".") will be ignored.');
        }
        $this->io->newLine();

        try {
            $indicesResp = $this->httpClient->request('GET', $this->elasticsearch.'/_cat/indices?format=json', [
                'timeout' => $this->timeout,
                'headers' => $this->headers,
            ]);
            $indices = $indicesResp->toArray();

            $aliasesResp = $this->httpClient->request('GET', $this->elasticsearch.'/_aliases', [
                'timeout' => $this->timeout,
                'headers' => $this->headers,
            ]);
            $aliasesMap = $aliasesResp->toArray(false);
        } catch (TransportExceptionInterface|HttpExceptionInterface $e) {
            $this->io->error(\sprintf('Error while calling Elasticsearch: %s', $e->getMessage()));

            return Command::FAILURE;
        }

        $allIndexNames = [];
        foreach ($indices as $row) {
            if (!isset($row['index'])) {
                continue;
            }
            $name = (string) $row['index'];
            if (!$this->includeSystem && \str_starts_with($name, '.')) {
                continue;
            }
            $allIndexNames[] = $name;
        }

        $orphans = [];
        foreach ($allIndexNames as $name) {
            $hasAliases = isset($aliasesMap[$name]['aliases']) && \count($aliasesMap[$name]['aliases']) > 0;
            if (!$hasAliases) {
                $orphans[] = $name;
            }
        }

        if ([] === $orphans) {
            $this->io->success('No orphan indices found. The cluster is clean 👍');

            return Command::SUCCESS;
        }

        $this->io->section(\sprintf('Found %d orphan indices', \count($orphans)));
        $this->io->listing($orphans);

        if (!$this->force) {
            $this->io->success('Dry-run completed. Add --force to actually delete these indices.');

            return Command::SUCCESS;
        }

        $this->io->section('Deleting orphan indices...');
        $deleted = [];
        $failed = [];

        foreach ($orphans as $index) {
            try {
                $resp = $this->httpClient->request('DELETE', $this->elasticsearch.'/'.\rawurlencode($index), [
                    'timeout' => $this->timeout,
                    'headers' => $this->headers,
                ]);
                $status = $resp->getStatusCode();
                if ($status >= 200 && $status < 300) {
                    $deleted[] = $index;
                    $this->io->writeln(\sprintf('<info>✔</info> Deleted: %s', $index));
                } else {
                    $failed[] = [$index, 'HTTP '.$status];
                    $this->io->writeln(\sprintf('<error>✘</error> Failed to delete %s (HTTP %d)', $index, $status));
                }
            } catch (TransportExceptionInterface|HttpExceptionInterface $e) {
                $failed[] = [$index, $e->getMessage()];
                $this->io->writeln(\sprintf('<error>✘</error> Failed to delete %s (%s)', $index, $e->getMessage()));
            }
        }

        $this->io->newLine();
        if ([] !== $deleted) {
            $this->io->success(\sprintf('%d indices deleted successfully.', \count($deleted)));
        }
        if ([] !== $failed) {
            $this->io->warning(\sprintf('%d deletions failed.', \count($failed)));

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
