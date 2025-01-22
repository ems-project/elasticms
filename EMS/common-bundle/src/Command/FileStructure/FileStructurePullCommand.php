<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Command\FileStructure;

use EMS\CommonBundle\Commands;
use EMS\CommonBundle\Common\Admin\AdminHelper;
use EMS\CommonBundle\Common\Command\AbstractCommand;
use EMS\CommonBundle\Contracts\File\FileManagerInterface;
use EMS\CommonBundle\Storage\Archive;
use EMS\CommonBundle\Storage\StorageManager;
use EMS\Helpers\Standard\Type;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

#[AsCommand(
    name: Commands::FILE_STRUCTURE_PULL,
    description: 'Pull an EMS archive into a local folder (and overwrite it)',
    hidden: false
)]
class FileStructurePullCommand extends AbstractCommand
{
    private const string ARGUMENT_ARCHIVE_HASH = 'hash';
    private const string ARGUMENT_FOLDER = 'folder';
    private const string OPTION_ADMIN = 'admin';

    private string $folderPath;
    private string $archiveHash;
    private FileManagerInterface $fileManager;

    public function __construct(
        private readonly AdminHelper $adminHelper,
        private readonly StorageManager $storageManager,
    ) {
        parent::__construct();
    }

    #[\Override]
    protected function configure(): void
    {
        parent::configure();
        $this
            ->addArgument(self::ARGUMENT_ARCHIVE_HASH, InputArgument::REQUIRED, 'Hash of the ElasticMS Archive')
            ->addArgument(self::ARGUMENT_FOLDER, InputArgument::REQUIRED, 'Target folder')
            ->addOption(self::OPTION_ADMIN, null, InputOption::VALUE_NONE, 'Pull from admin')
        ;
    }

    #[\Override]
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
        $this->archiveHash = $this->getArgumentString(self::ARGUMENT_ARCHIVE_HASH);
        $this->folderPath = $this->getArgumentString(self::ARGUMENT_FOLDER);
        $this->fileManager = match ($this->getOptionBool(self::OPTION_ADMIN)) {
            true => $this->adminHelper->getCoreApi()->file(),
            false => $this->storageManager,
        };
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title('EMS - File structure - Pull');

        $algo = $this->fileManager->getHashAlgo();
        $archiveFile = $this->fileManager->downloadFile($this->archiveHash);
        $archive = Archive::fromStructure(Type::string(\file_get_contents($archiveFile)), $algo);

        $done = [];
        $filesystem = new Filesystem();
        $filesystem->mkdir($this->folderPath);

        $finder = new Finder();
        $finder->in($this->folderPath)->files();
        foreach ($finder as $file) {
            $item = $archive->getByPath($file->getRelativePathname());
            if (null !== $item && $item->hash === \hash_file($algo, $file->getPathname())) {
                $done[] = $file->getRelativePathname();
                continue;
            }
            $filesystem->remove($file->getPathname());
        }

        $progressBar = $this->io->createProgressBar($archive->getCount());
        foreach ($archive->iterator() as $item) {
            if (\in_array($item->filename, $done, true)) {
                $progressBar->advance();
                continue;
            }
            $tempFile = $this->fileManager->downloadFile($item->hash);
            $explodedPath = \explode(\DIRECTORY_SEPARATOR, $this->folderPath.\DIRECTORY_SEPARATOR.$item->filename);
            \array_pop($explodedPath);
            $filesystem->mkdir(\implode(\DIRECTORY_SEPARATOR, $explodedPath));
            $filesystem->rename($tempFile, $this->folderPath.\DIRECTORY_SEPARATOR.$item->filename);
            $progressBar->advance();
        }
        $progressBar->finish();

        return self::EXECUTE_SUCCESS;
    }
}
