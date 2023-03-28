<?php

declare(strict_types=1);

namespace App\CLI\Command\MediaLibrary;

use App\CLI\Client\MediaLibrary\MediaLibrarySync;
use App\CLI\Commands;
use EMS\CommonBundle\Common\Admin\AdminHelper;
use EMS\CommonBundle\Common\Command\AbstractCommand;
use EMS\CommonBundle\Contracts\File\FileReaderInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class MediaLibrarySyncCommand extends AbstractCommand
{
    private bool $dryRun;
    private string $folder;

    protected static $defaultName = Commands::MEDIA_LIBRARY_SYNC;

    private const ARGUMENT_FOLDER = 'folder';
    private const OPTION_CONTENT_TYPE = 'content-type';
    private const OPTION_FOLDER_FIELD = 'folder-field';
    private const OPTION_PATH_FIELD = 'path-field';
    private const OPTION_FILE_FIELD = 'file-field';
    private const OPTION_DRY_RUN = 'dry-run';
    private const OPTION_METADATA_FILE = 'metadata-file';
    private const OPTION_LOCATE_ROW_EXPRESSION = 'locate-row-expression';
    private const OPTION_ONLY_MISSING = 'only-missing';
    private ?string $metadataFile;
    private string $locateRowExpression;
    private string $contentType;
    private string $folderField;
    private string $pathField;
    private string $fileField;
    private bool $onlyMissingFile;

    public function __construct(private readonly AdminHelper $adminHelper, private readonly FileReaderInterface $fileReader)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Synchronization files on media library for a given folder')
            ->addArgument(self::ARGUMENT_FOLDER, InputArgument::REQUIRED, 'Folder path')
            ->addOption(self::OPTION_CONTENT_TYPE, null, InputOption::VALUE_OPTIONAL, 'Media Library content type (default: media_file)', 'media_file')
            ->addOption(self::OPTION_FOLDER_FIELD, null, InputOption::VALUE_OPTIONAL, 'Media Library folder field (default: media_folder)', 'media_folder')
            ->addOption(self::OPTION_PATH_FIELD, null, InputOption::VALUE_OPTIONAL, 'Media Library path field (default: media_path)', 'media_path')
            ->addOption(self::OPTION_FILE_FIELD, null, InputOption::VALUE_OPTIONAL, 'Media Library file field (default: media_file)', 'media_file')
            ->addOption(self::OPTION_DRY_RUN, null, InputOption::VALUE_NONE, 'Just do a dry run')
            ->addOption(self::OPTION_METADATA_FILE, null, InputOption::VALUE_OPTIONAL, 'Path to a file containing metadata (CSV or  Excel)')
            ->addOption(self::OPTION_LOCATE_ROW_EXPRESSION, null, InputOption::VALUE_OPTIONAL, 'Expression language apply to excel rows in order to identify the file by its filename', "row['filename']")
            ->addOption(self::OPTION_ONLY_MISSING, null, InputOption::VALUE_NONE, 'Skip known files (already uploaded)')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
        $this->folder = $this->getArgumentString(self::ARGUMENT_FOLDER);
        $this->contentType = $this->getOptionString(self::OPTION_CONTENT_TYPE);
        $this->folderField = $this->getOptionString(self::OPTION_FOLDER_FIELD);
        $this->pathField = $this->getOptionString(self::OPTION_PATH_FIELD);
        $this->fileField = $this->getOptionString(self::OPTION_FILE_FIELD);
        $this->metadataFile = $this->getOptionStringNull(self::OPTION_METADATA_FILE);
        $this->locateRowExpression = $this->getOptionString(self::OPTION_LOCATE_ROW_EXPRESSION);
        $this->dryRun = $this->getOptionBool(self::OPTION_DRY_RUN);
        $this->onlyMissingFile = $this->getOptionBool(self::OPTION_ONLY_MISSING);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title('EMS Client - Media Library sync');
        $coreApi = $this->adminHelper->getCoreApi();

        if (!$coreApi->isAuthenticated()) {
            $this->io->error(\sprintf('Not authenticated for %s, run ems:admin:login', $this->adminHelper->getCoreApi()->getBaseUrl()));

            return self::EXECUTE_ERROR;
        }

        $mediaSync = new MediaLibrarySync($this->folder, $this->contentType, $this->folderField, $this->pathField, $this->fileField, $this->io, $this->dryRun, $coreApi, $this->fileReader, $this->onlyMissingFile);
        if (null !== $this->metadataFile) {
            $mediaSync->loadMetadata($this->metadataFile, $this->locateRowExpression);
        }
        $mediaSync->execute();

        return self::EXECUTE_SUCCESS;
    }
}
