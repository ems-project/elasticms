<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Command\Admin\Archive;

use Elastica\Query\BoolQuery;
use Elastica\Query\Term;
use EMS\CommonBundle\Commands;
use EMS\CommonBundle\Common\Admin\AdminHelper;
use EMS\CommonBundle\Common\Command\AbstractCommand;
use EMS\CommonBundle\Helper\EmsFields;
use EMS\CommonBundle\Search\Search;
use EMS\CommonBundle\Storage\Archive;
use EMS\CommonBundle\Storage\File\FileInterface;
use EMS\CommonBundle\Storage\File\MemoryFile;
use EMS\Helpers\Html\MimeTypes;
use EMS\Helpers\Standard\Json;
use EMS\Helpers\Standard\Type;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: Commands::ADMIN_ARCHIVE_UPLOAD,
    description: 'Upload a file structure, as an archive, into an admin server.',
    hidden: false
)]
class UploadCommand extends AbstractCommand
{
    private const FOLDER_PATH_ARGUMENT = 'folder-path';
    private const TARGET_PATH_ARGUMENT = 'target-path';
    private const MEDIA_LIBRARY_CONTENT_TYPE_OPTION = 'content-type';
    private const MEDIA_LIBRARY_PATH_FIELD_OPTION = 'path-field';
    private const MEDIA_LIBRARY_FOLDER_FIELD_OPTION = 'folder-field';
    private const MEDIA_LIBRARY_FILE_FIELD_OPTION = 'file-field';
    private const PRELOAD_OPTION = 'preload';
    private string $folderPath;
    private ?string $targetPath;
    private string $mediaLibraryContentType;
    private string $mediaLibraryPathField;
    private string $mediaLibraryFileField;
    private string $mediaLibraryFolderField;
    private bool $preloadCache;

    public function __construct(private readonly AdminHelper $adminHelper)
    {
        parent::__construct();
    }

    #[\Override]
    protected function configure(): void
    {
        parent::configure();
        $this
            ->addArgument(self::FOLDER_PATH_ARGUMENT, InputArgument::REQUIRED, 'Path to the folder to upload')
            ->addArgument(self::TARGET_PATH_ARGUMENT, InputArgument::OPTIONAL, 'Target path in the media library')
            ->addOption(self::MEDIA_LIBRARY_CONTENT_TYPE_OPTION, null, InputOption::VALUE_OPTIONAL, 'Content type for the media library', 'media_file')
            ->addOption(self::MEDIA_LIBRARY_PATH_FIELD_OPTION, null, InputOption::VALUE_OPTIONAL, 'Path field in the media library', 'media_path')
            ->addOption(self::MEDIA_LIBRARY_FILE_FIELD_OPTION, null, InputOption::VALUE_OPTIONAL, 'File field in the media library', 'media_file')
            ->addOption(self::MEDIA_LIBRARY_FOLDER_FIELD_OPTION, null, InputOption::VALUE_OPTIONAL, 'Folder field in the media library', 'media_folder')
            ->addOption(self::PRELOAD_OPTION, null, InputOption::VALUE_NONE, 'Preload the archive is storage caches')
        ;
    }

    #[\Override]
    public function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
        $this->adminHelper->setLogger(new ConsoleLogger($output));
        $this->folderPath = $this->getArgumentString(self::FOLDER_PATH_ARGUMENT);
        $this->targetPath = $this->getArgumentStringNull(self::TARGET_PATH_ARGUMENT);
        $this->mediaLibraryContentType = $this->getOptionString(self::MEDIA_LIBRARY_CONTENT_TYPE_OPTION);
        $this->mediaLibraryPathField = $this->getOptionString(self::MEDIA_LIBRARY_PATH_FIELD_OPTION);
        $this->mediaLibraryFileField = $this->getOptionString(self::MEDIA_LIBRARY_FILE_FIELD_OPTION);
        $this->mediaLibraryFolderField = $this->getOptionString(self::MEDIA_LIBRARY_FOLDER_FIELD_OPTION);
        $this->preloadCache = $this->getOptionBool(self::PRELOAD_OPTION);
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $coreApi = $this->adminHelper->getCoreApi();
        $this->io->title(\sprintf('Admin - Archive - Upload archive from folder %s', $this->folderPath));

        if (!$coreApi->isAuthenticated()) {
            $this->io->error(\sprintf('Not authenticated for %s, run emsch:local:login', $coreApi->getBaseUrl()));

            return self::EXECUTE_ERROR;
        }
        $archive = $this->uploadAsEmsArchive();
        $archiveHash = $this->adminHelper->getCoreApi()->file()->uploadContents($archive->getContent(), 'archive.json', MimeTypes::APPLICATION_JSON->value);
        $this->io->success(\sprintf('Folder archived with hash %s', $archiveHash));
        $this->uploadInMediaLibrary($archiveHash, \strlen($archive->getContent()));
        $this->preloadCache($archiveHash);

        return self::EXECUTE_SUCCESS;
    }

    private function uploadAsEmsArchive(): FileInterface
    {
        $fileApi = $this->adminHelper->getCoreApi()->file();
        $archive = Archive::fromDirectory($this->folderPath, $fileApi->getHashAlgo());
        $this->io->section(\sprintf('Start uploading %d files', $archive->getCount()));
        $this->io->progressStart($archive->getCount());
        foreach ($fileApi->heads(...$archive->getHashes()) as $hash) {
            if (true === $hash) {
                $this->io->progressAdvance();
                continue;
            }

            $file = $archive->getFirstFileByHash($hash);
            $uploadHash = $fileApi->uploadFile($this->folderPath.DIRECTORY_SEPARATOR.$file->filename);
            if ($uploadHash !== $hash) {
                throw new \RuntimeException(\sprintf('Mismatched between the computed hash (%s) and the hash of the uploaded file (%s) for the file %s', $hash, $uploadHash, $file->filename));
            }
            $this->io->progressAdvance();
        }
        $this->io->progressFinish();

        return new MemoryFile('archive.json', Json::encode($archive));
    }

    private function uploadInMediaLibrary(string $archiveHash, int $filesize): void
    {
        if (null === $this->targetPath) {
            return;
        }

        $this->io->section(\sprintf('Upload archive to %s', $this->targetPath));
        $path = \explode('/', $this->targetPath);
        $filename = \array_pop($path);
        if (isset($path[0]) && '' === $path[0]) {
            \array_shift($path);
        }
        $this->folders($path);
        $this->file($path, $filename, $archiveHash, $filesize);
    }

    /**
     * @param string[] $path
     */
    private function folders(array $path): void
    {
        $searchApi = $this->adminHelper->getCoreApi()->search();
        $mediaLibraryApi = $this->adminHelper->getCoreApi()->data($this->mediaLibraryContentType);
        $alias = $this->adminHelper->getCoreApi()->meta()->getDefaultContentTypeEnvironmentAlias($this->mediaLibraryContentType);
        $currentFolder = '/';
        foreach ($path as $folder) {
            if ('' === $folder) {
                throw new \RuntimeException('Empty folder path');
            }
            $searchQuery = new BoolQuery();
            $pathTerm = new Term();
            $pathTerm->setTerm($this->mediaLibraryPathField, $currentFolder.$folder);
            $searchQuery->addMust($pathTerm);
            $folderTerm = new Term();
            $folderTerm->setTerm($this->mediaLibraryFolderField, $currentFolder);
            $searchQuery->addMust($folderTerm);
            $search = new Search([$alias], $searchQuery);
            $search->setSources([$this->mediaLibraryFileField]);
            $response = $searchApi->search($search);
            if (0 === $response->getTotal()) {
                $mediaLibraryApi->index(null, [
                    $this->mediaLibraryPathField => $currentFolder.$folder,
                    $this->mediaLibraryFolderField => $currentFolder,
                ]);
            } elseif (1 === $response->getTotal()) {
                if (null !== $response->getDocument(0)->getEMSSource()->get($this->mediaLibraryFileField)) {
                    throw new \RuntimeException(\sprintf('%s/%s is a file not a folder', $currentFolder, $currentFolder));
                }
            } else {
                throw new \RuntimeException(\sprintf('Multiple media library with %d result(s) found', $response->getTotal()));
            }
            $currentFolder .= $folder.'/';
        }
    }

    /**
     * @param string[] $path
     */
    private function file(array $path, string $filename, string $hash, int $filesize): void
    {
        $searchApi = $this->adminHelper->getCoreApi()->search();
        $mediaLibraryApi = $this->adminHelper->getCoreApi()->data($this->mediaLibraryContentType);
        $alias = $this->adminHelper->getCoreApi()->meta()->getDefaultContentTypeEnvironmentAlias($this->mediaLibraryContentType);
        $folder = \implode('/', ['', ...$path, '']);
        $searchQuery = new BoolQuery();
        $pathTerm = new Term();
        $pathTerm->setTerm($this->mediaLibraryPathField, $folder.$filename);
        $searchQuery->addMust($pathTerm);
        $folderTerm = new Term();
        $folderTerm->setTerm($this->mediaLibraryFolderField, $folder);
        $searchQuery->addMust($folderTerm);
        $search = new Search([$alias], $searchQuery);
        $search->setSources([$this->mediaLibraryFileField]);
        $response = $searchApi->search($search);
        if (1 === $response->getTotal()) {
            $document = $response->getDocument(0);
            $ouuid = $document->getId();
            $currentFile = $document->getEMSSource()->get($this->mediaLibraryFileField);
            if (empty($currentFile)) {
                throw new \RuntimeException(\sprintf('%s is a directory', $folder.$filename));
            }
            if ($hash === Type::string($currentFile[EmsFields::CONTENT_FILE_HASH_FIELD])) {
                $this->io->comment('The existing archive was already up-to-date');

                return;
            }
        } elseif ($response->getTotal() > 1) {
            throw new \RuntimeException(\sprintf('Multiple media library with %d result(s) found', $response->getTotal()));
        }
        $mediaLibraryApi->index($ouuid ?? null, [
            $this->mediaLibraryPathField => $folder.$filename,
            $this->mediaLibraryFolderField => $folder,
            $this->mediaLibraryFileField => [
                EmsFields::CONTENT_FILE_HASH_FIELD => $hash,
                EmsFields::CONTENT_MIME_TYPE_FIELD => MimeTypes::APPLICATION_JSON,
                EmsFields::CONTENT_FILE_NAME_FIELD => $filename,
                EmsFields::CONTENT_FILE_SIZE_FIELD => $filesize,
            ],
        ]);
    }

    private function preloadCache(string $hash): void
    {
        if (!$this->preloadCache) {
            return;
        }
        $this->adminHelper->getCoreApi()->admin()->runCommand(\implode(' ', [Commands::LOAD_ARCHIVE_IN_CACHE, $hash]), $this->output);
    }
}
