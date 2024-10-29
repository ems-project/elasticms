<?php

declare(strict_types=1);

namespace App\CLI\Command\FileReader;

use App\CLI\Client\File\FileReaderImportConfig;
use App\CLI\Commands;
use EMS\CommonBundle\Common\Admin\AdminHelper;
use EMS\CommonBundle\Common\Command\AbstractCommand;
use EMS\CommonBundle\Contracts\File\FileReaderInterface;
use EMS\CommonBundle\Search\Search;
use EMS\CommonBundle\Storage\StorageManager;
use EMS\Helpers\Standard\Json;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

final class FileReaderImportCommand extends AbstractCommand
{
    protected static $defaultName = Commands::FILE_READER_IMPORT;

    private const ARGUMENT_FILE = 'file';
    private const ARGUMENT_CONTENT_TYPE = 'content-type';
    private const OPTION_CONFIG = 'config';
    private const OPTION_DRY_RUN = 'dry-run';

    private string $file;
    private string $contentType;
    private bool $dryRun;

    public function __construct(
        private readonly AdminHelper $adminHelper,
        private readonly StorageManager $storageManager,
        private readonly FileReaderInterface $fileReader
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Import an Excel file or a CSV file, one document per row')
            ->addArgument(self::ARGUMENT_FILE, InputArgument::REQUIRED, 'File path (xlsx or csv)')
            ->addArgument(self::ARGUMENT_CONTENT_TYPE, InputArgument::REQUIRED, 'Content type target')
            ->addOption(self::OPTION_CONFIG, null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Config(s) json, file path or hash', [])
            ->addOption(self::OPTION_DRY_RUN, null, InputOption::VALUE_NONE, 'Just do a dry run')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
        $this->file = $this->getArgumentString(self::ARGUMENT_FILE);
        $this->contentType = $this->getArgumentString(self::ARGUMENT_CONTENT_TYPE);
        $this->dryRun = $this->getOptionBool(self::OPTION_DRY_RUN);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->io->title('EMS Client - File reader importer');
            $coreApi = $this->adminHelper->getCoreApi();
            $contentTypeApi = $coreApi->data($this->contentType);

            if (!$coreApi->isAuthenticated()) {
                throw new \RuntimeException(\sprintf('Not authenticated for %s, run ems:admin:login', $this->adminHelper->getCoreApi()->getBaseUrl()));
            }

            $file = $this->storageManager->getFile($this->file);
            $config = $this->createConfig(...$this->getOptionStringArray(self::OPTION_CONFIG, false));

            $expressionLanguage = new ExpressionLanguage();
            $rows = $this->fileReader->getData($file->getFilename(), [
                'delimiter' => $config->delimiter,
                'encoding' => $config->encoding,
            ]);
            $header = \array_map('trim', $rows[0] ?? []);

            $ouuids = [];
            if ($config->deleteMissingDocuments) {
                $defaultAlias = $this->adminHelper->getCoreApi()->meta()->getDefaultContentTypeEnvironmentAlias($this->contentType);
                $search = new Search([$defaultAlias]);
                $search->setSources(['_id']);
                $search->setContentTypes([$this->contentType]);

                foreach ($this->adminHelper->getCoreApi()->search()->scroll($search) as $hit) {
                    $ouuids[$hit->getOuuid()] = true;
                }
            }

            $counter = 0;
            $progressBar = $this->io->createProgressBar(\count($rows) - 1);
            foreach ($rows as $key => $rowValues) {
                if (0 === $key) {
                    continue;
                }
                $row = [];
                $empty = true;
                foreach ($rowValues as $cellKey => $cell) {
                    $row[$header[$cellKey] ?? $cellKey] = $cell;
                    $empty = $empty && (null === $cell);
                }
                if ($empty) {
                    $progressBar->advance();
                    continue;
                }

                $ouuid = null === $config->ouuidExpression ? null : $expressionLanguage->evaluate($config->ouuidExpression, [
                    'row' => $row,
                ]);
                if ('null' !== $config->ouuidExpression && $config->generateHash) {
                    $ouuid = \sha1(\sprintf('FileReaderImport:%s:%s', $this->contentType, $ouuid));
                }
                unset($ouuids[$ouuid]);

                if ($this->dryRun) {
                    $progressBar->advance();
                    continue;
                }

                if ('null' === $config->ouuidExpression) {
                    $draft = $contentTypeApi->create([
                        '_sync_metadata' => $row,
                    ]);
                } elseif ($contentTypeApi->head($ouuid)) {
                    $draft = $contentTypeApi->update($ouuid, [
                        '_sync_metadata' => $row,
                    ]);
                } else {
                    $draft = $contentTypeApi->create([
                        '_sync_metadata' => $row,
                    ], $ouuid);
                }
                $contentTypeApi->finalize($draft->getRevisionId());
                $progressBar->advance();
                ++$counter;
            }
            $progressBar->finish();
            $this->io->newLine(2);
            $this->io->text(\sprintf('%d lines have been imported', $counter));

            if ($this->dryRun && \count($ouuids) > 0) {
                $this->io->newLine(2);
                $this->io->warning(\sprintf('%d documents are missing in the source file and will be deleted without the %s option', \count($ouuids), self::OPTION_DRY_RUN));
            } elseif (\count($ouuids) > 0) {
                $this->io->newLine(2);
                $this->io->section(\sprintf('%d documents have not been updated and will be deleted', \count($ouuids)));
                $progressBar = $this->io->createProgressBar(\count($ouuids));
                foreach ($ouuids as $ouuid => $data) {
                    $contentTypeApi->delete($ouuid);
                    $progressBar->advance();
                }
                $progressBar->finish();
            }

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
            default => Json::decode($this->storageManager->getFile($input)->getContent())
        }, $inputs);

        return FileReaderImportConfig::createFromArray(
            config: \array_merge_recursive(...$configs)
        );
    }
}
