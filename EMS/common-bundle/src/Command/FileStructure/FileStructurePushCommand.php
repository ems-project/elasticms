<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Command\FileStructure;

use EMS\CommonBundle\Commands;
use EMS\CommonBundle\Common\Admin\AdminHelper;
use EMS\CommonBundle\Common\Command\AbstractCommand;
use EMS\CommonBundle\Contracts\File\FileManagerInterface;
use EMS\CommonBundle\Storage\Archive;
use EMS\CommonBundle\Storage\StorageManager;
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
    private const OPTION_QUIET = 'quiet';
    private const OPTION_QUIET_SHORTCUT = 'q';
    private string $folderPath;
    private FileManagerInterface $fileManager;
    private bool $quiet;

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
            ->addOption(self::OPTION_QUIET, self::OPTION_QUIET_SHORTCUT, InputOption::VALUE_NONE, 'only displays the archive hash (if succeed)')
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
        $this->quiet = $this->getOptionBool(self::OPTION_QUIET);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->quiet) {
            $this->io->title('EMS - File structure - Push');
        }
        $algo = $this->fileManager->getHashAlgo();

        if (!$this->quiet) {
            $this->io->section('Building archive');
        }
        $archive = Archive::fromDirectory($this->folderPath, $algo);

        if (!$this->quiet) {
            $this->io->section('Pushing archive');
        }
        $progressBar = $this->io->createProgressBar($archive->getCount());
        $failedCount = 0;
        foreach ($this->fileManager->heads(...$archive->getHashes()) as $hash) {
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
        $hash = $this->fileManager->uploadContents(Json::encode($archive), 'archive.json', MimeTypes::APPLICATION_JSON->value);
        $this->io->newLine();
        if (0 !== $failedCount) {
            $this->io->error(\sprintf('%d files faced an issue while uploading, please retry.', $failedCount));

            return self::EXECUTE_ERROR;
        }

        if ($this->quiet) {
            $this->io->write($hash);
        } else {
            $this->io->success(\sprintf('Archive %s have been uploaded with the directory content of %s', $hash, $this->folderPath));
        }

        return self::EXECUTE_SUCCESS;
    }
}
