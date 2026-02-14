<?php

declare(strict_types=1);

namespace App\CLI\Command\Import;

use App\CLI\Client\File\ImportConfig;
use Elastica\Query\AbstractQuery;
use Elastica\Query\BoolQuery;
use Elastica\Query\Exists;
use Elastica\Query\Terms;
use EMS\CommonBundle\Common\Admin\AdminHelper;
use EMS\CommonBundle\Common\Command\AbstractCommand;
use EMS\CommonBundle\Contracts\CoreApi\Endpoint\Data\DataInterface;
use EMS\CommonBundle\Contracts\ExpressionServiceInterface;
use EMS\CommonBundle\Search\Search;
use EMS\CommonBundle\Storage\File\FileInterface;
use EMS\CommonBundle\Storage\NotFoundException;
use EMS\CommonBundle\Storage\StorageManager;
use EMS\Helpers\Standard\Hash;
use EMS\Helpers\Standard\Json;
use EMS\Helpers\Standard\UuidGenerator;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractImportCommand extends AbstractCommand
{
    private const string ARGUMENT_CONTENT_TYPE = 'content-type';
    private const string OPTION_CONFIG = 'config';
    private const string OPTION_DRY_RUN = 'dry-run';
    private const string OPTION_MERGE = 'merge';
    private const string OPTION_LAZY = 'lazy';
    private const string OPTION_DIGEST_FIELD = 'digest-field';

    private const string OPTION_FLUSH_SIZE = 'flush-size';
    private const string OPTION_CHUNK_SIZE = 'chunk-size';
    private const string OPTION_SCROLL_SIZE = 'scroll-size';

    private string $contentType;
    private bool $dryRun;
    private bool $merge;
    private bool $lazy;
    private ?string $digestField = null;

    private int $flushSize;
    private int $scrollSize;
    private int $chunkSize;

    private int $countIndex = 0;
    private int $countDigest = 0;
    private int $countExclude = 0;

    public function __construct(
        private readonly AdminHelper $adminHelper,
        private readonly StorageManager $storageManager,
        private readonly ExpressionServiceInterface $expressionService,
    ) {
        parent::__construct();
    }

    #[\Override]
    protected function configure(): void
    {
        $this
            ->addArgument(self::ARGUMENT_CONTENT_TYPE, InputArgument::REQUIRED, 'Content type target')
            ->addOption(self::OPTION_CONFIG, null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Config(s) json, file path or hash', [])
            ->addOption(self::OPTION_DRY_RUN, null, InputOption::VALUE_NONE, 'Just do a dry run')
            ->addOption(self::OPTION_MERGE, null, InputOption::VALUE_REQUIRED, 'Perform a merge or replace', true)
            ->addOption(self::OPTION_FLUSH_SIZE, null, InputOption::VALUE_REQUIRED, 'Flush size for the queue', 100)
            ->addOption(self::OPTION_CHUNK_SIZE, null, InputOption::VALUE_REQUIRED, 'Chunk size for processing rows', 100)
            ->addOption(self::OPTION_SCROLL_SIZE, null, InputOption::VALUE_REQUIRED, 'Search scroll size', 100)
            ->addOption(self::OPTION_LAZY, null, InputOption::VALUE_NONE, 'Lazy index will only call post-processing on source element')
            ->addOption(self::OPTION_DIGEST_FIELD, null, InputOption::VALUE_REQUIRED, 'Only index not digested rows')
        ;
    }

    #[\Override]
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
        $this->contentType = $this->getArgumentString(self::ARGUMENT_CONTENT_TYPE);
        $this->dryRun = $this->getOptionBool(self::OPTION_DRY_RUN);
        $this->merge = $this->getOptionBool(self::OPTION_MERGE);
        $this->lazy = $this->getOptionBool(self::OPTION_LAZY);
        $this->digestField = $this->getOptionStringNull(self::OPTION_DIGEST_FIELD);

        $this->flushSize = $this->getOptionInt(self::OPTION_FLUSH_SIZE);
        $this->chunkSize = $this->getOptionInt(self::OPTION_CHUNK_SIZE);
        $this->scrollSize = $this->getOptionInt(self::OPTION_SCROLL_SIZE);
    }

    public function import(ImportConfig $config, \Generator $records): void
    {
        $coreApi = $this->adminHelper->getCoreApi();
        $contentTypeApi = $coreApi->data($this->contentType);
        if (!$coreApi->isAuthenticated()) {
            throw new \RuntimeException(\sprintf('Not authenticated for %s, run ems:admin:login', $this->adminHelper->getCoreApi()->getBaseUrl()));
        }

        $ouuids = $config->deleteMissingDocuments ? $this->searchExistingOuuids($config) : [];

        $progressBar = $this->io->createProgressBar();
        $progressBar->start();

        $queue = $coreApi->queue($this->flushSize);

        foreach ($this->processInChunk($config, $records) as $docs) {
            $indexOuuids = \array_keys($docs);
            $ouuids = \array_diff($ouuids, $indexOuuids);

            $docs = $this->filterDigested($docs);

            foreach ($docs as $ouuid => $rawData) {
                if ($this->excludeExpression($config, $ouuid, $rawData)) {
                    ++$this->countExclude;
                    continue;
                }

                if (!$this->dryRun) {
                    $queue->add($contentTypeApi->indexAsync(
                        ouuid: $ouuid,
                        rawData: $rawData,
                        merge: $this->merge,
                        lazy: $this->lazy
                    ));
                }
                ++$this->countIndex;
            }
            $progressBar->advance(\count($indexOuuids));
        }

        $queue->flush();
        $progressBar->finish();
        $this->io->newLine();

        $notReadable = \count($records->getReturn());
        if ($notReadable > 0) {
            $this->io->warning(\sprintf('Could not read %d records', $notReadable));
        }

        if (!$this->dryRun && $config->deleteMissingDocuments && [] !== $ouuids) {
            $this->deleteMissingDocuments($contentTypeApi, ...$ouuids);
        }

        $this->alignEnvironments($config);

        $this->io->definitionList(
            'Summary',
            ['Index' => $this->countIndex],
            ['Excluded' => $this->countExclude],
            ['Digested' => $this->countDigest],
            ['Delete' => \count($ouuids)]
        );
    }

    protected function getImportConfig(): ImportConfig
    {
        $inputs = $this->getOptionStringArray(self::OPTION_CONFIG, false);

        $configs = \array_map(fn (string $input) => match (true) {
            Json::isJson($input) => Json::decode($input),
            default => Json::decode($this->getFile($input)->getContent()),
        }, $inputs);

        return ImportConfig::createFromArray(
            config: \array_merge(...$configs)
        );
    }

    protected function getFile(string $fileIdentifier): FileInterface
    {
        try {
            return $this->storageManager->getFile($fileIdentifier);
        } catch (NotFoundException) {
            return $this->adminHelper->getCoreApi()->file()->getFile($fileIdentifier);
        }
    }

    private function processInChunk(ImportConfig $config, \Generator $records): \Generator
    {
        $chunks = [];

        foreach ($records as $row) {
            if ($config->lowercaseHeaders) {
                $row = \array_change_key_case($row);
            }

            $ouuid = $this->createOuuid($config, $row) ?? Uuid::uuid4()->toString();

            $rawData = $config->defaultData;
            $rawData['_sync_metadata'] = $row;

            if (null !== $ouuidVersionExpression = $config->ouuidVersionExpression) {
                $ouuidVersionValue = $this->expressionService->evaluateToString($ouuidVersionExpression, ['row' => $row]);
                if (null === $ouuidVersionValue) {
                    throw new \RuntimeException(\sprintf('Could not make version ouuid from expression: %s', $ouuidVersionExpression));
                }

                $rawData['_version_uuid'] = UuidGenerator::fromValue($ouuidVersionValue);
            }

            if (null !== $this->digestField) {
                $rowDigest = $this->storageManager->computeDataHash($row);
                $rawData[$this->digestField] = $rowDigest;
            }

            $chunks[$ouuid] = $rawData;

            if (\count($chunks) === $this->chunkSize) {
                yield $chunks;
                $chunks = [];
            }
        }

        if ([] !== $chunks) {
            yield $chunks;
        }
    }

    /**
     * @param array<int, array<mixed>> $row
     */
    private function createOuuid(ImportConfig $config, array $row): ?string
    {
        if (null === $config->ouuidExpression) {
            return null;
        }

        $ouuid = $this->expressionService->evaluateToString($config->ouuidExpression, ['row' => $row]);
        $prefix = $config->ouuidPrefix;

        return (string) match (true) {
            $config->generateOuuid => UuidGenerator::fromValue(($prefix ?? '').$ouuid),
            null !== $prefix => Hash::string($prefix.$ouuid),
            $config->generateHash => Hash::string(\sprintf('FileReaderImport:%s:%s', $this->contentType, $ouuid)),
            default => $ouuid,
        };
    }

    /**
     * @param array<string, string> $rawData
     */
    private function excludeExpression(ImportConfig $config, string $ouuid, array $rawData): bool
    {
        if (null === $expression = $config->excludeExpression) {
            return false;
        }

        return $this->expressionService->evaluateToBool($expression, [
            'ouuid' => $ouuid,
            'row' => $rawData['_sync_metadata'],
        ]);
    }

    /**
     * @param array<string, array<string, string>> $docs
     *
     * @return array<string, array<string, string>>
     */
    public function filterDigested(array $docs): array
    {
        if (null === $this->digestField) {
            return $docs;
        }

        $result = [];
        $digested = $this->searchDigested($this->digestField, ...\array_keys($docs));

        foreach ($docs as $ouuid => $rawData) {
            $digestRawdata = $rawData[$this->digestField] ?? null;
            $digestSearch = $digested[$ouuid] ?? null;

            if ($digestRawdata && $digestRawdata === $digestSearch) {
                ++$this->countDigest;
                continue;
            }

            $result[$ouuid] = $rawData;
        }

        return $result;
    }

    private function deleteMissingDocuments(DataInterface $api, string ...$ouuids): void
    {
        $this->io->newLine(2);
        $this->io->section(\sprintf('%d documents have not been updated and will be deleted', \count($ouuids)));

        $progressBar = $this->io->createProgressBar(\count($ouuids));
        foreach ($ouuids as $ouuid) {
            $api->delete($ouuid);
            $progressBar->advance();
        }
        $progressBar->finish();
        $this->io->newLine();
    }

    /**
     * @return array<string, string>
     */
    private function searchDigested(string $field, string ...$ouuids): array
    {
        $digested = [];

        $searchQuery = new BoolQuery();
        $searchQuery->addMust(new Terms('_id', \array_values($ouuids)));
        $searchQuery->addMust(new Exists($field));

        $search = $this->createSearch($searchQuery);
        $search->setSources([$field]);

        foreach ($this->adminHelper->getCoreApi()->search()->scroll($search, $this->scrollSize) as $hit) {
            $digested[$hit->getOuuid()] = $hit->getValue($field);
        }

        return $digested;
    }

    /**
     * @return string[]
     */
    private function searchExistingOuuids(ImportConfig $config): array
    {
        $query = $config->query ? new BoolQuery()->addMust($config->query) : null;

        $ouuids = [];
        $search = $this->createSearch($query);

        foreach ($this->adminHelper->getCoreApi()->search()->scroll($search, $this->scrollSize) as $hit) {
            $ouuids[] = $hit->getOuuid();
        }

        return $ouuids;
    }

    private function createSearch(?AbstractQuery $query = null): Search
    {
        $index = $this->adminHelper->getCoreApi()->meta()->getDefaultContentTypeEnvironmentAlias($this->contentType);

        $search = new Search([$index], $query);
        $search->setContentTypes([$this->contentType]);

        return $search;
    }

    private function alignEnvironments(ImportConfig $config): void
    {
        $alignEnvironments = $config->alignEnvironments;

        if ([] === $alignEnvironments) {
            return;
        }

        $this->io->newLine(2);
        $this->io->section('Align environments');

        $adminApi = $this->adminHelper->getCoreApi()->admin();

        foreach ($alignEnvironments as $align) {
            $options = ['--force'];
            if ($config->query) {
                $options[] = \sprintf("--search-query='%s'", Json::encode($config->query));
            }

            $command = [
                'emsco:environment:align',
                ...$options,
                '--',
                $align['source'],
                $align['target'],
            ];

            $adminApi->runCommand(\implode(' ', $command), $this->output);
        }
    }
}
