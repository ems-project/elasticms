<?php

declare(strict_types=1);

namespace App\CLI\Command\MediaLibrary;

use App\CLI\Client\MediaLibrary\MediaLibrarySync;
use App\CLI\Client\MediaLibrary\MediaLibrarySyncOptions;
use App\CLI\Commands;
use App\CLI\Helper\Tika\TikaHelper;
use EMS\CommonBundle\Common\Admin\AdminHelper;
use EMS\CommonBundle\Common\Command\AbstractCommand;
use EMS\CommonBundle\Contracts\ExpressionServiceInterface;
use EMS\CommonBundle\Contracts\File\FileReaderInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: Commands::MEDIA_LIBRARY_SYNC,
    description: 'Synchronization files on media library for a given folder.',
    hidden: false
)]
final class MediaLibrarySyncCommand extends AbstractCommand
{
    private const string ARGUMENT_FOLDER = 'folder';
    private const string OPTION_CONTENT_TYPE = 'content-type';
    private const string OPTION_FOLDER_FIELD = 'folder-field';
    private const string OPTION_PATH_FIELD = 'path-field';
    private const string OPTION_FILE_FIELD = 'file-field';
    private const string OPTION_DRY_RUN = 'dry-run';
    private const string OPTION_METADATA_FILE = 'metadata-file';
    private const string OPTION_LOCATE_ROW_EXPRESSION = 'locate-row-expression';
    private const string OPTION_ONLY_MISSING = 'only-missing';
    private const string OPTION_ONLY_METADATA_FILE = 'only-metadata-file';
    private const string OPTION_TIKA = 'tika';
    private const string OPTION_TIKA_BASE_URL = 'tika-base-url';
    private const string OPTION_TIKA_CACHE_FOLDER = 'tika-cache-folder';
    private const string OPTION_MAX_CONTENT_SIZE = 'max-content-size';
    private const string OPTION_HASH_FOLDER = 'hash-folder';
    private const string OPTION_HASH_METADATA_FILE = 'hash-metadata-file';
    private const string OPTION_TARGET_FOLDER = 'target-folder';
    private const string OPTION_FORCE_EXTRACT = 'force-extract';
    private const string OPTION_EXTRACT_SIZE = 'max-extract-size';

    private bool $tika;
    private ?string $tikaBaseUrl = null;
    private ?string $tikaCacheFolder = null;

    private MediaLibrarySyncOptions $options;

    public function __construct(
        private readonly AdminHelper $adminHelper,
        private readonly FileReaderInterface $fileReader,
        private readonly ExpressionServiceInterface $expressionService,
    ) {
        parent::__construct();
    }

    #[\Override]
    protected function configure(): void
    {
        $this
            ->addArgument(self::ARGUMENT_FOLDER, InputArgument::REQUIRED, 'Folder path')
            ->addOption(self::OPTION_CONTENT_TYPE, null, InputOption::VALUE_OPTIONAL, 'Media Library content type (default: media_file)', 'media_file')
            ->addOption(self::OPTION_FOLDER_FIELD, null, InputOption::VALUE_OPTIONAL, 'Media Library folder field (default: media_folder)', 'media_folder')
            ->addOption(self::OPTION_PATH_FIELD, null, InputOption::VALUE_OPTIONAL, 'Media Library path field (default: media_path)', 'media_path')
            ->addOption(self::OPTION_FILE_FIELD, null, InputOption::VALUE_OPTIONAL, 'Media Library file field (default: media_file)', 'media_file')
            ->addOption(self::OPTION_DRY_RUN, null, InputOption::VALUE_NONE, 'Just do a dry run')
            ->addOption(self::OPTION_METADATA_FILE, null, InputOption::VALUE_OPTIONAL, 'Path to a file containing metadata (CSV or Excel)')
            ->addOption(self::OPTION_LOCATE_ROW_EXPRESSION, null, InputOption::VALUE_OPTIONAL, 'Expression language apply to excel rows in order to identify the file by its filename', "row['filename']")
            ->addOption(self::OPTION_ONLY_MISSING, null, InputOption::VALUE_NONE, 'Skip known files (already uploaded)')
            ->addOption(self::OPTION_ONLY_METADATA_FILE, null, InputOption::VALUE_NONE, 'Skip files that are not referenced in the metadata file')
            ->addOption(self::OPTION_TIKA, null, InputOption::VALUE_NONE, 'Add a Tika extract for IndexedFile')
            ->addOption(self::OPTION_TIKA_BASE_URL, null, InputOption::VALUE_OPTIONAL, 'Tika\'s server base url. If not defined a JVM will be instantiated')
            ->addOption(self::OPTION_TIKA_CACHE_FOLDER, null, InputOption::VALUE_OPTIONAL, 'Folder where tika extraction can be cached')
            ->addOption(self::OPTION_MAX_CONTENT_SIZE, null, InputOption::VALUE_OPTIONAL, 'Will keep the x first characters extracted by Tika to be indexed', 5120)
            ->addOption(self::OPTION_HASH_FOLDER, null, InputOption::VALUE_NONE, 'Provide a hash for folder argument (zip file)')
            ->addOption(self::OPTION_HASH_METADATA_FILE, null, InputOption::VALUE_NONE, 'Provide a hash for option metadata file (CSV or Excel)')
            ->addOption(self::OPTION_TARGET_FOLDER, null, InputOption::VALUE_OPTIONAL, 'Base path to sync in the media library. Must start by a / and should ends also with a /', '/')
            ->addOption(self::OPTION_FORCE_EXTRACT, null, InputOption::VALUE_NONE, 'Force tika extraction')
            ->addOption(self::OPTION_EXTRACT_SIZE, null, InputOption::VALUE_OPTIONAL, 'Max file size for extraction', 64 * 1024 * 1024)
        ;
    }

    #[\Override]
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);

        $this->options = new MediaLibrarySyncOptions(
            folder: $this->getArgumentString(self::ARGUMENT_FOLDER),
            contentType: $this->getOptionString(self::OPTION_CONTENT_TYPE),
            folderField: $this->getOptionString(self::OPTION_FOLDER_FIELD),
            pathField: $this->getOptionString(self::OPTION_PATH_FIELD),
            fileField: $this->getOptionString(self::OPTION_FILE_FIELD),
            metaDataFile: $this->getOptionStringNull(self::OPTION_METADATA_FILE),
            locateRowExpression: $this->getOptionString(self::OPTION_LOCATE_ROW_EXPRESSION),
            targetFolder: $this->getOptionString(self::OPTION_TARGET_FOLDER),
            dryRun: $this->getOptionBool(self::OPTION_DRY_RUN),
            onlyMissingFile: $this->getOptionBool(self::OPTION_ONLY_MISSING),
            onlyMetadataFile: $this->getOptionBool(self::OPTION_ONLY_METADATA_FILE),
            hashFolder: $this->getOptionBool(self::OPTION_HASH_FOLDER),
            hashMetaDataFile: $this->getOptionBool(self::OPTION_HASH_METADATA_FILE),
            forceExtract: $this->getOptionBool(self::OPTION_FORCE_EXTRACT),
            maxContentSize: $this->getOptionInt(self::OPTION_MAX_CONTENT_SIZE),
            maxFileSizeExtract: $this->getOptionInt(self::OPTION_EXTRACT_SIZE),
        );

        $this->tika = $this->getOptionBool(self::OPTION_TIKA);
        $this->tikaBaseUrl = $this->getOptionStringNull(self::OPTION_TIKA_BASE_URL);
        $this->tikaCacheFolder = $this->getOptionStringNull(self::OPTION_TIKA_CACHE_FOLDER);
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title('EMS Client - Media Library sync');
        $coreApi = $this->adminHelper->getCoreApi();

        if (!$coreApi->isAuthenticated()) {
            $this->io->error(\sprintf('Not authenticated for %s, run ems:admin:login', $this->adminHelper->getCoreApi()->getBaseUrl()));

            return self::EXECUTE_ERROR;
        }

        $mediaSync = new MediaLibrarySync(
            $this->options,
            $this->io,
            $coreApi,
            $this->fileReader,
            $this->expressionService
        );

        if ($this->tika) {
            $tikaHelper = $this->tikaBaseUrl ? TikaHelper::initTikaServer($this->tikaBaseUrl, $this->tikaCacheFolder) : TikaHelper::initTikaJar($this->tikaCacheFolder);
            $mediaSync->setTikaHelper($tikaHelper);
        }

        $mediaSync->execute();

        return self::EXECUTE_SUCCESS;
    }
}
