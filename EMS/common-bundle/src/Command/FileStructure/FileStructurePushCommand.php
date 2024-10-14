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
    private string $folderPath;
    private FileManagerInterface $fileManager;

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
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title('EMS - File structure - Push');
        $algo = $this->fileManager->getHashAlgo();

        $this->io->section('Building archive');
        $archive = Archive::fromDirectory($this->folderPath, $algo);

        $this->io->section('Pushing archive');
        $progressBar = $this->io->createProgressBar($archive->getCount());
        foreach ($this->fileManager->heads(...$archive->getHashes()) as $hash) {
            $file = $archive->getFirstFileByHash($hash);
            $uploadHash = $this->fileManager->uploadFile($this->folderPath.DIRECTORY_SEPARATOR.$file->filename);
            if ($uploadHash !== $hash) {
                throw new \RuntimeException(\sprintf('Mismatched between the computed hash (%s) and the hash of the uploaded file (%s) for the file %s', $hash, $uploadHash, $file->filename));
            }
            $progressBar->advance();
        }
        $progressBar->finish();
        $hash = $this->fileManager->uploadContents(Json::encode($archive), 'archive.json', MimeTypes::APPLICATION_JSON->value);
        $this->io->newLine();
        $this->io->success(\sprintf('Archive %s have been uploaded with the directory content of %s', $hash, $this->folderPath));

        return self::EXECUTE_SUCCESS;
    }
}
