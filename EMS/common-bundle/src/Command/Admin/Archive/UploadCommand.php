<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Command\Admin\Archive;

use EMS\CommonBundle\Commands;
use EMS\CommonBundle\Common\Admin\AdminHelper;
use EMS\CommonBundle\Common\Command\AbstractCommand;
use EMS\CommonBundle\Storage\Archive;
use EMS\Helpers\Html\MimeTypes;
use EMS\Helpers\Standard\Json;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: Commands::ADMIN_ARCHIVE_UPLOAD,
    description: 'Upload a file structure, as an archive, into an admin server.',
    hidden: false
)]
class UploadCommand extends AbstractCommand
{
    public const FOLDER_PATH_ARGUMENT = 'folder-path';
    private string $folderPath;

    public function __construct(private readonly AdminHelper $adminHelper)
    {
        parent::__construct();
    }

    #[\Override]
    protected function configure(): void
    {
        parent::configure();
        $this->addArgument(self::FOLDER_PATH_ARGUMENT, InputArgument::REQUIRED, 'Path to the folder to upload');
    }

    #[\Override]
    public function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
        $this->adminHelper->setLogger(new ConsoleLogger($output));
        $this->folderPath = $this->getArgumentString(self::FOLDER_PATH_ARGUMENT);
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
        $archiveHash = $this->uploadAsEmsArchive();
        $this->io->success(\sprintf('Folder archived with hash %s', $archiveHash));

        return self::EXECUTE_SUCCESS;
    }

    private function uploadAsEmsArchive(): string
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

        return $fileApi->uploadContents(Json::encode($archive), 'archive.json', MimeTypes::APPLICATION_JSON->value);
    }
}
