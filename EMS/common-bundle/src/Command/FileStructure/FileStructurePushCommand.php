<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Command\FileStructure;

use EMS\CommonBundle\Commands;
use EMS\CommonBundle\Common\Admin\AdminHelper;
use EMS\CommonBundle\Common\Command\AbstractCommand;
use EMS\CommonBundle\Contracts\File\FileManagerInterface;
use EMS\CommonBundle\Storage\Archive;
use EMS\CommonBundle\Storage\StorageManager;
use EMS\Helpers\File\File;
use EMS\Helpers\Html\MimeTypes;
use EMS\Helpers\Standard\Json;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class FileStructurePushCommand extends AbstractCommand
{
    protected static $defaultName = Commands::FILE_STRUCTURE_PUSH;
    private const ARGUMENT_FOLDER = 'folder';
    private const OPTION_ADMIN = 'admin';
    private const OPTION_CHUNK_SIZE = 'chunk-size';
    private const OPTION_SAVE_HASH_FILENAME = 'save-hash-filename';
    private const DEFAULT_SAVE_HASH_FILE = '.hash';
    private string $folderPath;
    private FileManagerInterface $fileManager;
    private int $chunkSize;
    private string $saveHashFilename;

    public function __construct(
        private readonly AdminHelper $adminHelper,
        private readonly StorageManager $storageManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        parent::configure();
        $this
            ->setDescription('Push an EMS Archive file structure into a EMS Admin storage services (via the API)')
            ->addArgument(self::ARGUMENT_FOLDER, InputArgument::REQUIRED, 'Source folder')
            ->addOption(self::OPTION_ADMIN, null, InputOption::VALUE_NONE, 'Push to admin')
            ->addOption(self::OPTION_CHUNK_SIZE, null, InputOption::VALUE_OPTIONAL, 'Set the heads method chunk size', FileManagerInterface::HEADS_CHUNK_SIZE)
            ->addOption(self::OPTION_SAVE_HASH_FILENAME, null, InputOption::VALUE_OPTIONAL, 'File where to save the structure hash within the source folder (used to avoid head request). Delete the file to force recheck all files.', self::DEFAULT_SAVE_HASH_FILE)
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
        $this->folderPath = $this->getArgumentString(self::ARGUMENT_FOLDER);
        $this->fileManager = match ($this->getOptionBool(self::OPTION_ADMIN)) {
            true => $this->adminHelper->getCoreApi()->file(),
            false => $this->storageManager,
        };
        $this->chunkSize = $this->getOptionInt(self::OPTION_CHUNK_SIZE);
        $this->saveHashFilename = $this->getOptionString(self::OPTION_SAVE_HASH_FILENAME);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title('EMS - File structure - Push');
        $algo = $this->fileManager->getHashAlgo();

        $this->io->section('Building archive');
        $progressBar = $this->io->createProgressBar();
        $archive = Archive::fromDirectory($this->folderPath, $algo, $this->output->isQuiet() ? null : function ($maxSteps, $progress) use ($progressBar) {
            if ($maxSteps !== $progressBar->getMaxSteps()) {
                $progressBar->setMaxSteps($maxSteps);
            }
            $progressBar->setProgress($progress);
        });
        $progressBar->finish();
        $this->io->newLine();

        $this->io->section('Comparing with previous archive');
        $previousArchive = null;
        $hashFilename = \implode(DIRECTORY_SEPARATOR, [$this->folderPath, $this->saveHashFilename]);
        if (\file_exists($hashFilename)) {
            $previousArchive = Archive::fromStructure($this->fileManager->getContents(File::fromFilename($hashFilename)->getContents()), $algo);
        }
        $diffArchive = $archive->diff($previousArchive);

        $this->io->section('Pushing archive');
        $progressBar = $this->io->createProgressBar($archive->getCount());
        $failedCount = 0;
        if ($this->chunkSize < 1) {
            throw new \RuntimeException(\sprintf('Chunk size must greater than 0, %d given', $this->chunkSize));
        }
        $this->fileManager->setHeadChunkSize($this->chunkSize);
        foreach ($this->fileManager->heads(...$diffArchive->getHashes()) as $hash) {
            if (true === $hash) {
                $progressBar->advance();
                continue;
            }
            $file = $archive->getFirstFileByHash($hash);
            try {
                $uploadHash = $this->fileManager->uploadFile($this->folderPath.DIRECTORY_SEPARATOR.$file->filename);
                if ($uploadHash !== $hash) {
                    throw new \RuntimeException(\sprintf('Mismatched between the computed hash (%s) and the hash of the uploaded file (%s) for the file %s', $hash, $uploadHash, $file->filename));
                }
            } catch (\Throwable) {
                $this->io->error(\sprintf('Error while saving the file %s', $file->filename));
                ++$failedCount;
            }
            $progressBar->advance();
        }
        $progressBar->finish();
        $this->io->newLine();
        $hash = $this->fileManager->uploadContents(Json::encode($archive), 'archive.json', MimeTypes::APPLICATION_JSON->value);
        if (0 !== $failedCount) {
            $this->io->error(\sprintf('%d files faced an issue while uploading, please retry.', $failedCount));

            return self::EXECUTE_ERROR;
        }

        $this->io->section('Building cache');
        $progressBar = $this->io->createProgressBar($archive->getCount());
        $this->fileManager->loadArchiveItemsInCache($hash, $archive, $this->output->isQuiet() ? null : function () use ($progressBar) {
            $progressBar->advance();
        });
        $progressBar->finish();
        $this->io->newLine();

        \file_put_contents($hashFilename, $hash);

        if ($this->output->isQuiet()) {
            echo $hash;
        } else {
            $this->io->success(\sprintf('Archive %s have been uploaded with the directory content of %s', $hash, $this->folderPath));
        }

        return self::EXECUTE_SUCCESS;
    }
}
