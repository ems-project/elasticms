<?php

declare(strict_types=1);

namespace App\CLI\Command\FileReader;

use App\CLI\Client\File\FileReaderImportConfig;
use App\CLI\Commands;
use EMS\CommonBundle\Common\Admin\AdminHelper;
use EMS\CommonBundle\Common\Command\AbstractCommand;
use EMS\CommonBundle\Contracts\CoreApi\Endpoint\Data\DataInterface;
use EMS\CommonBundle\Contracts\File\FileReaderInterface;
use EMS\CommonBundle\Search\Search;
use EMS\CommonBundle\Storage\File\FileInterface;
use EMS\CommonBundle\Storage\NotFoundException;
use EMS\CommonBundle\Storage\StorageManager;
use EMS\Helpers\Standard\Hash;
use EMS\Helpers\Standard\Json;
use EMS\Helpers\Standard\UuidGenerator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

#[AsCommand(
    name: Commands::FILE_READER_IMPORT,
    description: 'Import an Excel file or a CSV file, one document per row.',
    hidden: false
)]
final class FileReaderImportCommand extends AbstractCommand
{
    private const ARGUMENT_FILE = 'file';
    private const ARGUMENT_CONTENT_TYPE = 'content-type';
    private const OPTION_CONFIG = 'config';
    private const OPTION_DRY_RUN = 'dry-run';
    private const OPTION_LIMIT = 'limit';
    private const OPTION_FLUSH_SIZE = 'flush-size';
    private const OPTION_MERGE = 'merge';

    private string $file;
    private string $contentType;
    private bool $dryRun;
    private bool $merge;
    private int $flushSize;
    private ?int $limit;
    private ExpressionLanguage $expressionLanguage;

    public function __construct(
        private readonly AdminHelper $adminHelper,
        private readonly StorageManager $storageManager,
        private readonly FileReaderInterface $fileReader,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(self::ARGUMENT_FILE, InputArgument::REQUIRED, 'File path (xlsx or csv)')
            ->addArgument(self::ARGUMENT_CONTENT_TYPE, InputArgument::REQUIRED, 'Content type target')
            ->addOption(self::OPTION_CONFIG, null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Config(s) json, file path or hash', [])
            ->addOption(self::OPTION_DRY_RUN, null, InputOption::VALUE_NONE, 'Just do a dry run')
            ->addOption(self::OPTION_MERGE, null, InputOption::VALUE_REQUIRED, 'Perform a merge or replace', true)
            ->addOption(self::OPTION_FLUSH_SIZE, null, InputOption::VALUE_REQUIRED, 'Flush size for the queue', 100)
            ->addOption(self::OPTION_LIMIT, null, InputOption::VALUE_REQUIRED, 'Limit the rows')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
        $this->file = $this->getArgumentString(self::ARGUMENT_FILE);
        $this->contentType = $this->getArgumentString(self::ARGUMENT_CONTENT_TYPE);
        $this->dryRun = $this->getOptionBool(self::OPTION_DRY_RUN);
        $this->merge = $this->getOptionBool(self::OPTION_MERGE);
        $this->flushSize = $this->getOptionInt(self::OPTION_FLUSH_SIZE);
        $this->limit = $this->getOptionIntNull(self::OPTION_LIMIT);

        $this->expressionLanguage = new ExpressionLanguage();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->io->title('EMS CLI - File reader - Import');

            $coreApi = $this->adminHelper->getCoreApi();
            $contentTypeApi = $coreApi->data($this->contentType);
            if (!$coreApi->isAuthenticated()) {
                throw new \RuntimeException(\sprintf('Not authenticated for %s, run ems:admin:login', $this->adminHelper->getCoreApi()->getBaseUrl()));
            }

            $config = $this->createConfig(...$this->getOptionStringArray(self::OPTION_CONFIG, false));

            $cells = $this->fileReader->readCells($this->getFile($this->file)->getFilename(), [
                'delimiter' => $config->delimiter,
                'encoding' => $config->encoding,
                'exclude_rows' => $config->excludeRows,
                'limit' => $this->limit,
            ]);

            $ouuids = $config->deleteMissingDocuments ? $this->searchExistingOuuids() : [];

            $progressBar = $this->io->createProgressBar();
            $count = 0;
            $queue = $coreApi->queue($this->flushSize)->addFlushCallback(fn () => $progressBar->advance());

            foreach ($cells as $row) {
                $ouuid = $this->createOuuid($config, $row);

                $rawData = $config->defaultData;
                $rawData['_sync_metadata'] = $row;

                if (null !== $ouuidVersionExpression = $config->ouuidVersionExpression) {
                    $rawData['_version_uuid'] = UuidGenerator::fromValue(
                        value: $this->expressionLanguage->evaluate($ouuidVersionExpression, ['row' => $row])
                    );
                }

                if ($ouuid) {
                    unset($ouuids[$ouuid]);
                }

                if (!$this->dryRun) {
                    $queue->add($contentTypeApi->indexAsync(ouuid: $ouuid, rawData: $rawData, merge: $this->merge));
                }

                ++$count;
            }

            $queue->flush();
            $progressBar->finish();
            $this->io->newLine();

            $notReadable = \count($cells->getReturn());
            if ($notReadable > 0) {
                $this->io->warning(\sprintf('Could not read %d records', $notReadable));
            }

            if (!$this->dryRun && $config->deleteMissingDocuments && \count($ouuids) > 0) {
                $this->deleteMissingDocuments($contentTypeApi, ...\array_keys($ouuids));
            }

            $this->io->definitionList('Summary',
                ['Index' => $count],
                ['Delete' => \count($ouuids)]
            );

            return self::EXECUTE_SUCCESS;
        } catch (\Throwable $e) {
            $this->io->error($e->getMessage());

            return self::EXECUTE_ERROR;
        }
    }

    private function createConfig(string ...$inputs): FileReaderImportConfig
    {
        $configs = \array_map(fn (string $input) => match (true) {
            Json::isJson($input) => Json::decode($input),
            default => Json::decode($this->getFile($input)->getContent()),
        }, $inputs);

        return FileReaderImportConfig::createFromArray(
            config: \array_merge(...$configs)
        );
    }

    /**
     * @param array<int, array<mixed>> $syncMetaData
     */
    private function createOuuid(FileReaderImportConfig $config, array $syncMetaData): ?string
    {
        if (null === $config->ouuidExpression) {
            return null;
        }

        $ouuid = $this->expressionLanguage->evaluate($config->ouuidExpression, ['row' => $syncMetaData]);
        $prefix = $config->ouuidPrefix;

        return match (true) {
            $config->generateOuuid => (string) UuidGenerator::fromValue(($prefix ?? '').$ouuid),
            null !== $prefix => Hash::string($prefix.$ouuid),
            $config->generateHash => Hash::string(\sprintf('FileReaderImport:%s:%s', $this->contentType, $ouuid)),
            default => $ouuid,
        };
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
     * @return array<string, bool>
     */
    private function searchExistingOuuids(): array
    {
        $ouuids = [];
        $search = new Search([
            $this->adminHelper->getCoreApi()->meta()->getDefaultContentTypeEnvironmentAlias($this->contentType),
        ]);
        $search->setSources(['_id']);
        $search->setContentTypes([$this->contentType]);

        foreach ($this->adminHelper->getCoreApi()->search()->scroll($search) as $hit) {
            $ouuids[$hit->getOuuid()] = true;
        }

        return $ouuids;
    }

    private function getFile(string $fileIdentifier): FileInterface
    {
        try {
            return $this->storageManager->getFile($fileIdentifier);
        } catch (NotFoundException) {
            return $this->adminHelper->getCoreApi()->file()->getFile($fileIdentifier);
        }
    }
}
