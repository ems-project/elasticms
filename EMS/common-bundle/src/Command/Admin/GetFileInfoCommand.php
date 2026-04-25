<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Command\Admin;

use EMS\CommonBundle\Commands;
use EMS\CommonBundle\Common\Admin\AdminHelper;
use EMS\CommonBundle\Common\Command\AbstractCommand;
use EMS\CommonBundle\Contracts\CoreApi\Endpoint\File\FileInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: Commands::ADMIN_FILE_INFO,
    description: 'Get, from the admin, file information for a provided hash',
    hidden: false
)]
class GetFileInfoCommand extends AbstractCommand
{
    public const string FILE_HASH = 'file-hash';
    public const string LAST_UPLOADED = 'last-uploaded';
    private FileInterface $fileApi;
    private string $fileHash;
    private bool $lastUploaded;

    public function __construct(private readonly AdminHelper $adminHelper)
    {
        parent::__construct();
    }

    #[\Override]
    protected function configure(): void
    {
        parent::configure();
        $this->addArgument(self::FILE_HASH, InputArgument::REQUIRED, 'Hash a the file that you wish to retrieve information from');
        $this->addOption(self::LAST_UPLOADED, null, InputOption::VALUE_NONE, 'Get information from last uploaded file. By default, it gets information from first uploaded file');
    }

    #[\Override]
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
        $this->fileApi = $this->adminHelper->getCoreApi()->file();
        $this->fileHash = $this->getArgumentString(self::FILE_HASH);
        $this->lastUploaded = $this->getOptionBool(self::LAST_UPLOADED);
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title(\sprintf('Information of asset %s', $this->fileHash));
        $fileInfo = $this->fileApi->getFileInfo($this->fileHash, !$this->lastUploaded);
        dump($fileInfo);

        return self::SUCCESS;
    }
}
