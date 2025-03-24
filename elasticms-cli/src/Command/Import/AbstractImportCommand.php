<?php

declare(strict_types=1);

namespace App\CLI\Command\Import;

use App\CLI\Client\File\ImportConfig;
use EMS\CommonBundle\Common\Admin\AdminHelper;
use EMS\CommonBundle\Common\Command\AbstractCommand;
use EMS\CommonBundle\Contracts\CoreApi\Endpoint\Data\DataInterface;
use EMS\CommonBundle\Search\Search;
use EMS\CommonBundle\Storage\File\FileInterface;
use EMS\CommonBundle\Storage\NotFoundException;
use EMS\CommonBundle\Storage\StorageManager;
use EMS\Helpers\Standard\Hash;
use EMS\Helpers\Standard\Json;
use EMS\Helpers\Standard\UuidGenerator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

abstract class AbstractImportCommand extends AbstractCommand
{
    private const string ARGUMENT_CONTENT_TYPE = 'content-type';
    private const string OPTION_CONFIG = 'config';
    private const string OPTION_DRY_RUN = 'dry-run';
    private const string OPTION_LIMIT = 'limit';
    private const string OPTION_FLUSH_SIZE = 'flush-size';
    private const string OPTION_SCROLL_SIZE = 'scroll-size';
    private const string OPTION_MERGE = 'merge';
    private const string OPTION_LAZY = 'lazy';
    private const string OPTION_DIGEST = 'digest';

    protected string $contentType;
    protected bool $dryRun;
    protected bool $merge;
    protected bool $lazy;
    protected int $flushSize;
    protected int $scrollSize;
    protected ?int $limit = null;
    private ?string $digestField = null;
    private ExpressionLanguage $expressionLanguage;

    public function __construct(
        private readonly AdminHelper $adminHelper,
        private readonly StorageManager $storageManager,
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
            ->addOption(self::OPTION_SCROLL_SIZE, null, InputOption::VALUE_REQUIRED, 'Scroll size for searching existing', 100)
            ->addOption(self::OPTION_LIMIT, null, InputOption::VALUE_REQUIRED, 'Limit the rows')
            ->addOption(self::OPTION_LAZY, null, InputOption::VALUE_NONE, 'Lazy index will only call post-processing on source element')
            ->addOption(self::OPTION_DIGEST, null, InputOption::VALUE_REQUIRED, 'Use a digest field')
        ;
    }

    #[\Override]
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
        $this->contentType = $this->getArgumentString(self::ARGUMENT_CONTENT_TYPE);
        $this->dryRun = $this->getOptionBool(self::OPTION_DRY_RUN);
        $this->merge = $this->getOptionBool(self::OPTION_MERGE);
        $this->flushSize = $this->getOptionInt(self::OPTION_FLUSH_SIZE);
        $this->scrollSize = $this->getOptionInt(self::OPTION_SCROLL_SIZE);
        $this->limit = $this->getOptionIntNull(self::OPTION_LIMIT);
        $this->lazy = $this->getOptionBool(self::OPTION_LAZY);
        $this->digestField = $this->getOptionStringNull(self::OPTION_DIGEST);

        $this->expressionLanguage = new ExpressionLanguage();
    }

    public function import(ImportConfig $config, \Generator $records): void
    {
        $coreApi = $this->adminHelper->getCoreApi();
        $contentTypeApi = $coreApi->data($this->contentType);
        if (!$coreApi->isAuthenticated()) {
            throw new \RuntimeException(\sprintf('Not authenticated for %s, run ems:admin:login', $this->adminHelper->getCoreApi()->getBaseUrl()));
        }

        $existing = $this->searchExisting($config);

        $progressBar = $this->io->createProgressBar();
        $count = 0;
        $countDigested = 0;
        $queue = $coreApi->queue($this->flushSize)->addFlushCallback(fn () => $progressBar->advance());

        foreach ($records as $row) {
            $ouuid = $this->createOuuid($config, $row);

            $rawData = $config->defaultData;
            $rawData['_sync_metadata'] = $row;

            if (null !== $ouuidVersionExpression = $config->ouuidVersionExpression) {
                $rawData['_version_uuid'] = UuidGenerator::fromValue(
                    value: $this->expressionLanguage->evaluate($ouuidVersionExpression, ['row' => $row])
                );
            }

            if ($ouuid) {
                $isDigested = $this->digest($row, $rawData, $existing[$ouuid] ?? null);
                unset($existing[$ouuid]);

                if ($isDigested) {
                    ++$countDigested;
                    continue;
                }
            }

            if (!$this->dryRun) {
                $queue->add($contentTypeApi->indexAsync(
                    ouuid: $ouuid,
                    rawData: $rawData,
                    merge: $this->merge,
                    lazy: $this->lazy
                ));
            }

            ++$count;
        }

        $queue->flush();
        $progressBar->finish();
        $this->io->newLine();

        $notReadable = \count($records->getReturn());
        if ($notReadable > 0) {
            $this->io->warning(\sprintf('Could not read %d records', $notReadable));
        }

        if (!$this->dryRun && $config->deleteMissingDocuments && \count($existing) > 0) {
            $this->deleteMissingDocuments($contentTypeApi, ...\array_keys($existing));
        }

        $this->io->definitionList(
            'Summary',
            ['Index' => $count],
            ['Digested' => $countDigested],
            ['Delete' => \count($existing)]
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

    /**
     * @param array<int, array<mixed>> $row
     */
    private function createOuuid(ImportConfig $config, array $row): ?string
    {
        if (null === $config->ouuidExpression) {
            return null;
        }

        $ouuid = $this->expressionLanguage->evaluate($config->ouuidExpression, ['row' => $row]);
        $prefix = $config->ouuidPrefix;

        return (string) match (true) {
            $config->generateOuuid => UuidGenerator::fromValue(($prefix ?? '').$ouuid),
            null !== $prefix => Hash::string($prefix.$ouuid),
            $config->generateHash => Hash::string(\sprintf('FileReaderImport:%s:%s', $this->contentType, $ouuid)),
            default => $ouuid,
        };
    }

    /**
     * @param array<int, array<mixed>> $row
     * @param array<string, mixed>     $rawData
     */
    public function digest(array $row, array &$rawData, ?string $existingDigest): bool
    {
        if (null === $this->digestField) {
            return false;
        }

        $rowDigest = $this->storageManager->computeDataHash($row);
        $rawData[$this->digestField] = $rowDigest;

        return $existingDigest && $existingDigest === $rowDigest;
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
     * @return array<string, ?string>
     */
    private function searchExisting(ImportConfig $config): array
    {
        if (false === $config->deleteMissingDocuments && null === $this->digestField) {
            return [];
        }

        $ouuids = [];
        $search = new Search([
            $this->adminHelper->getCoreApi()->meta()->getDefaultContentTypeEnvironmentAlias($this->contentType),
        ]);
        $search->setSources($this->digestField ? ['_id', $this->digestField] : ['_id']);
        $search->setContentTypes([$this->contentType]);

        foreach ($this->adminHelper->getCoreApi()->search()->scroll($search, $this->scrollSize) as $hit) {
            $ouuids[$hit->getOuuid()] = $this->digestField ? $hit->getValue($this->digestField) : null;
        }

        return $ouuids;
    }
}
