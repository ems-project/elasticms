<?php

declare(strict_types=1);

namespace App\CLI\Command\Document;

use App\CLI\Client\Data\Data;
use App\CLI\Client\Document\Update\DocumentUpdateConfig;
use App\CLI\Client\Document\Update\DocumentUpdater;
use App\CLI\Commands;
use EMS\CommonBundle\Common\Admin\AdminHelper;
use EMS\CommonBundle\Common\Command\AbstractCommand;
use EMS\CommonBundle\Contracts\File\FileReaderInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: Commands::DOCUMENTS_UPDATE,
    description: 'Update documents from excel or csv file with custom configuration.',
    hidden: false
)]
final class DocumentUpdateCommand extends AbstractCommand
{
    private string $configFile;
    private string $dataFilePath;

    private int $dataOffset;
    private ?int $dataLength = null;
    private bool $dataSkipFirstRow;
    private bool $dryRun;

    private const ARGUMENT_DATA_FILE = 'data-file';
    private const ARGUMENT_CONFIG_FILE = 'config-file';
    private const OPTION_DATA_OFFSET = 'data-offset';
    private const OPTION_DATA_LENGTH = 'data-length';
    private const OPTION_DATA_SKIP_FIRST_ROW = 'data-skip-first';
    private const OPTION_DRY_RUN = 'dry-run';

    public function __construct(private readonly AdminHelper $adminHelper, private readonly FileReaderInterface $fileReader)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(self::ARGUMENT_CONFIG_FILE, InputArgument::REQUIRED, 'Config file (json)')
            ->addArgument(self::ARGUMENT_DATA_FILE, InputArgument::REQUIRED, 'Data file (excel or csv)')
            ->addOption(self::OPTION_DATA_OFFSET, null, InputOption::VALUE_REQUIRED, 'Offset data', 0)
            ->addOption(self::OPTION_DATA_LENGTH, null, InputOption::VALUE_REQUIRED, 'Length data to parse')
            ->addOption(self::OPTION_DATA_SKIP_FIRST_ROW, null, InputOption::VALUE_OPTIONAL, 'Skip data header', true)
            ->addOption(self::OPTION_DRY_RUN, null, InputOption::VALUE_NONE, 'Just do a dry run')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
        $this->configFile = $this->getArgumentString(self::ARGUMENT_CONFIG_FILE);
        $this->dataFilePath = $this->getArgumentString(self::ARGUMENT_DATA_FILE);

        $this->dataOffset = $this->getOptionInt(self::OPTION_DATA_OFFSET);
        $this->dataLength = $this->getOptionIntNull(self::OPTION_DATA_LENGTH);
        $this->dataSkipFirstRow = $this->getOptionBool(self::OPTION_DATA_SKIP_FIRST_ROW);
        $this->dryRun = $this->getOptionBool(self::OPTION_DRY_RUN);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title('EMS Client - update documents');
        $coreApi = $this->adminHelper->getCoreApi();

        if (!$coreApi->isAuthenticated()) {
            $this->io->error(\sprintf('Not authenticated for %s, run ems:admin:login', $this->adminHelper->getCoreApi()->getBaseUrl()));

            return self::EXECUTE_ERROR;
        }

        $config = DocumentUpdateConfig::fromFile($this->configFile);

        $this->io->section('Reading data');
        $dataArray = $this->fileReader->getData(
            filename: $this->dataFilePath,
            options: ['exclude_rows' => $this->dataSkipFirstRow ? [0] : []]
        );

        $data = new Data($dataArray);
        $this->io->writeln(\sprintf('Loaded data in memory: %d rows', \count($data)));

        if ($this->dataOffset || $this->dataLength) {
            $data->slice($this->dataOffset, $this->dataLength);
            $this->io->writeln(\sprintf('Sliced data: %d rows (start %d)', \count($data), $this->dataOffset));
        }

        $documentUpdater = new DocumentUpdater($data, $config, $coreApi, $this->io, $this->dryRun);
        $documentUpdater
            ->executeColumnTransformers()
            ->execute()
        ;

        return self::EXECUTE_SUCCESS;
    }
}
