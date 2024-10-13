<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Command\FileStructure;

use EMS\CommonBundle\Commands;
use EMS\CommonBundle\Common\Admin\AdminHelper;
use EMS\CommonBundle\Common\Command\AbstractCommand;
use EMS\CommonBundle\Contracts\CoreApi\CoreApiInterface;
use EMS\CommonBundle\Storage\Archive;
use EMS\Helpers\Html\MimeTypes;
use EMS\Helpers\Standard\Json;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FileStructurePushCommand extends AbstractCommand
{
    protected static $defaultName = Commands::FILE_STRUCTURE_PUSH;
    private const ARGUMENT_FOLDER = 'folder';
    private string $folderPath;
    private CoreApiInterface $coreApi;

    public function __construct(private readonly AdminHelper $adminHelper)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        parent::configure();
        $this->setDescription('Push an EMS Archive file structure into a EMS Admin storage services (via the API)');
        $this->addArgument(self::ARGUMENT_FOLDER, InputArgument::REQUIRED, 'Source folder');
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
        $this->folderPath = $this->getArgumentString(self::ARGUMENT_FOLDER);
        $this->coreApi = $this->adminHelper->getCoreApi();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $algo = $this->coreApi->file()->getHashAlgo();
        $archive = Archive::fromDirectory($this->folderPath, $algo);
        $progressBar = $this->io->createProgressBar($archive->getCount());
        foreach ($this->coreApi->file()->heads(...$archive->getHashes()) as $hash) {
            $file = $archive->getFirstFileByHash($hash);
            $uploadHash = $this->coreApi->file()->uploadFile($this->folderPath.DIRECTORY_SEPARATOR.$file->filename);
            if ($uploadHash !== $hash) {
                throw new \RuntimeException(\sprintf('Mismatched between the computed hash (%s) and the hash of the uploaded file (%s) for the file %s', $hash, $uploadHash, $file->filename));
            }
            $progressBar->advance();
        }
        $progressBar->finish();
        $hash = $this->coreApi->file()->uploadContents(Json::encode($archive), 'archive.json', MimeTypes::APPLICATION_JSON->value);
        $this->io->newLine();
        $this->io->success(\sprintf('Archive %s have been uploaded with the directory content of %s', $hash, $this->folderPath));

        return self::EXECUTE_SUCCESS;
    }
}
