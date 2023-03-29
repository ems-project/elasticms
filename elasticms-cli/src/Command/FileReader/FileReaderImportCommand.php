<?php

declare(strict_types=1);

namespace App\CLI\Command\FileReader;

use App\CLI\Commands;
use EMS\CommonBundle\Common\Admin\AdminHelper;
use EMS\CommonBundle\Common\Command\AbstractCommand;
use EMS\CommonBundle\Contracts\File\FileReaderInterface;
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
    private const OPTION_OUUID_EXPRESSION = 'ouuid-expression';
    private const OPTION_DRY_RUN = 'dry-run';
    private string $ouuidExpression;
    private string $contentType;
    private string $file;
    private bool $dryRun;

    public function __construct(private readonly AdminHelper $adminHelper, private readonly FileReaderInterface $fileReader)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Synchronization files on media library for a given folder')
            ->addArgument(self::ARGUMENT_FILE, InputArgument::REQUIRED, 'File path (xlsx or csv)')
            ->addArgument(self::ARGUMENT_CONTENT_TYPE, InputArgument::REQUIRED, 'Content type target')
            ->addOption(self::OPTION_DRY_RUN, null, InputOption::VALUE_NONE, 'Just do a dry run')
            ->addOption(self::OPTION_OUUID_EXPRESSION, null, InputOption::VALUE_OPTIONAL, 'Expression language apply to excel rows in order to identify the document by its ouuid', "row['ouuid']")
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
        $this->file = $this->getArgumentString(self::ARGUMENT_FILE);
        $this->contentType = $this->getArgumentString(self::ARGUMENT_CONTENT_TYPE);
        $this->ouuidExpression = $this->getOptionString(self::OPTION_OUUID_EXPRESSION);
        $this->dryRun = $this->getOptionBool(self::OPTION_DRY_RUN);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title('EMS Client - File reader importer');
        $coreApi = $this->adminHelper->getCoreApi();
        $contentTypeApi = $coreApi->data($this->contentType);

        if (!$coreApi->isAuthenticated()) {
            $this->io->error(\sprintf('Not authenticated for %s, run ems:admin:login', $this->adminHelper->getCoreApi()->getBaseUrl()));

            return self::EXECUTE_ERROR;
        }

        $expressionLanguage = new ExpressionLanguage();
        $rows = $this->fileReader->getData($this->file);
        $header = $rows[0] ?? [];

        $progressBar = $this->io->createProgressBar(\count($rows) - 1);
        foreach ($rows as $key => $value) {
            if (0 === $key) {
                continue;
            }
            $row = [];
            foreach ($value as $key => $cell) {
                $row[$header[$key] ?? $key] = $cell;
            }

            $ouuid = $expressionLanguage->evaluate($this->ouuidExpression, [
                'row' => $row,
            ]);
            if ($this->dryRun) {
                $progressBar->advance();
                continue;
            }
            if ($contentTypeApi->head($ouuid)) {
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
        }
        $progressBar->finish();

        return self::EXECUTE_SUCCESS;
    }
}
