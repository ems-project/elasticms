<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Command\FileStructure;

use EMS\CommonBundle\Commands;
use EMS\CommonBundle\Common\Admin\AdminHelper;
use EMS\CommonBundle\Common\Command\AbstractCommand;
use EMS\CommonBundle\Contracts\CoreApi\CoreApiInterface;
use EMS\CommonBundle\Storage\Archive;
use EMS\Helpers\Standard\Type;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class FileStructurePullCommand extends AbstractCommand
{
    protected static $defaultName = Commands::FILE_STRUCTURE_PULL;
    private const ARGUMENT_ARCHIVE_HASH = 'hash';
    private const ARGUMENT_FOLDER = 'folder';
    private string $folderPath;
    private CoreApiInterface $coreApi;
    private string $archiveHash;

    public function __construct(private readonly AdminHelper $adminHelper)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        parent::configure();
        $this->setDescription('Pull an EMS archive into a local folder (and overwrite it)');
        $this->addArgument(self::ARGUMENT_ARCHIVE_HASH, InputArgument::REQUIRED, 'Hash of the ElasticMS Archive');
        $this->addArgument(self::ARGUMENT_FOLDER, InputArgument::REQUIRED, 'Target folder');
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
        $this->archiveHash = $this->getArgumentString(self::ARGUMENT_ARCHIVE_HASH);
        $this->folderPath = $this->getArgumentString(self::ARGUMENT_FOLDER);
        $this->coreApi = $this->adminHelper->getCoreApi();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $algo = $this->coreApi->file()->getHashAlgo();
        $archiveFile = $this->coreApi->file()->downloadFile($this->archiveHash);
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
            $tempFile = $this->coreApi->file()->downloadFile($item->hash);
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
