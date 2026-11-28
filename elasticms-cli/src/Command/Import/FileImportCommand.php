<?php

declare(strict_types=1);

namespace App\CLI\Command\Import;

use App\CLI\Commands;
use EMS\CommonBundle\Common\Admin\AdminHelper;
use EMS\CommonBundle\Contracts\ExpressionServiceInterface;
use EMS\CommonBundle\Contracts\File\FileReaderInterface;
use EMS\CommonBundle\Helper\MimeTypeHelper;
use EMS\CommonBundle\Storage\StorageManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: Commands::IMPORT_FILE,
    description: 'Import an Excel file or a CSV file, one document per row.',
    aliases: ['emscli:file-reader:import'],
    hidden: false
)]
final class FileImportCommand extends AbstractImportCommand
{
    private const string ARGUMENT_FILE = 'file';
    private const string OPTION_LIMIT = 'limit';

    private string $file;
    private ?int $limit = null;

    public function __construct(
        private readonly FileReaderInterface $fileReader,
        AdminHelper $adminHelper,
        StorageManager $storageManager,
        ExpressionServiceInterface $expressionService,
    ) {
        parent::__construct($adminHelper, $storageManager, $expressionService);
    }

    #[\Override]
    protected function configure(): void
    {
        $this
            ->addArgument(self::ARGUMENT_FILE, InputArgument::REQUIRED, 'File path (xlsx or csv)')
            ->addOption(self::OPTION_LIMIT, null, InputOption::VALUE_REQUIRED, 'Limit the rows')
        ;
        parent::configure();
    }

    #[\Override]
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);

        $this->file = $this->getArgumentString(self::ARGUMENT_FILE);
        $this->limit = $this->getOptionIntNull(self::OPTION_LIMIT);
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->io->title('EMS CLI - Import - File');

            $config = $this->getImportConfig();
            $file = $this->getFile($this->file);
            $mimeType = $config->mimeType ?? MimeTypeHelper::getInstance()->guessMimeType($file->getFilename());

            $cells = $this->fileReader->readCells($file->getFilename(), [
                'mime_type' => $mimeType,
                'delimiter' => $config->delimiter,
                'encoding' => $config->encoding,
                'exclude_rows' => $config->excludeRows,
                'limit' => $this->limit,
            ]);

            $this->import($config, $cells);

            return self::EXECUTE_SUCCESS;
        } catch (\Throwable $throwable) {
            $this->io->error($throwable->getMessage());

            return self::EXECUTE_ERROR;
        }
    }
}
