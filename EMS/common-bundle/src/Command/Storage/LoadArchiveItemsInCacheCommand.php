<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Command\Storage;

use EMS\CommonBundle\Commands;
use EMS\CommonBundle\Common\Command\AbstractCommand;
use EMS\CommonBundle\Helper\MimeTypeHelper;
use EMS\CommonBundle\Storage\Archive;
use EMS\CommonBundle\Storage\StorageManager;
use EMS\Helpers\File\TempDirectory;
use EMS\Helpers\File\TempFile;
use EMS\Helpers\Html\MimeTypes;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class LoadArchiveItemsInCacheCommand extends AbstractCommand
{
    public const ARGUMENT_ARCHIVE_HASH = 'archive-hash';
    public const OPTION_CONTINUE = 'continue';
    protected static $defaultName = Commands::LOAD_ARCHIVE_IN_CACHE;
    private string $archiveHash;
    private int $continue;

    public function __construct(private readonly StorageManager $storageManager)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        parent::configure();
        $this
            ->setDescription('Load archive\'s items in cache')
            ->addArgument(self::ARGUMENT_ARCHIVE_HASH, InputArgument::REQUIRED, 'Hash of the archive file')
            ->addOption(self::OPTION_CONTINUE, null, InputOption::VALUE_OPTIONAL, 'Restart the load in cache from the specified item in the archive', 0)
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
        $this->archiveHash = $this->getArgumentString(self::ARGUMENT_ARCHIVE_HASH);
        $this->continue = $this->getOptionInt(self::OPTION_CONTINUE);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title('Load archive\'s items in storage cache');

        $this->io->section('Downloading archive');

        $progressBar = $this->io->createProgressBar($this->storageManager->getSize($this->archiveHash));

        $archiveFile = TempFile::create()->loadFromStream($this->storageManager->getStream($this->archiveHash), $this->output->isQuiet() ? null : function ($size) use ($progressBar): void {
            $progressBar->advance($size);
        });
        $mimeType = MimeTypeHelper::getInstance()->guessMimeType($archiveFile->path);
        $this->io->newLine();

        switch ($mimeType) {
            case MimeTypes::APPLICATION_ZIP->value:
            case MimeTypes::APPLICATION_GZIP->value:
                $this->loadZipArchive($archiveFile);
                break;
            case MimeTypes::APPLICATION_JSON->value:
                $this->loadEmsArchive($archiveFile);
                break;
            default:
                throw new \RuntimeException(\sprintf('Archive format %s not supported', $mimeType));
        }

        return self::EXECUTE_SUCCESS;
    }

    private function loadEmsArchive(TempFile $tempFile): void
    {
        $archive = Archive::fromStructure($tempFile->getContents(), $this->storageManager->getHashAlgo());
        $progressBar = $this->io->createProgressBar($archive->getCount());
        $this->storageManager->loadArchiveItemsInCache($this->archiveHash, $archive->skip($this->continue), $this->output->isQuiet() ? null : function () use ($progressBar) {
            $progressBar->advance();
        });
        $progressBar->finish();
        $this->io->newLine();
        $this->io->writeln(\sprintf('%d files have been pushed in cache', $archive->getCount()));
    }

    private function loadZipArchive(TempFile $archiveFile): void
    {
        $dir = TempDirectory::createFromZipArchive($archiveFile->path);
        $archiveFile->clean();
        $finder = new Finder();
        $finder->in($dir->path)->files();
        $this->io->progressStart($finder->count());
        $mimeTypeHelper = MimeTypeHelper::getInstance();
        $counter = 0;
        /** @var SplFileInfo $file */
        foreach ($finder as $file) {
            $mimeType = $mimeTypeHelper->guessMimeType($file->getPathname());
            $counter += $this->storageManager->addFileInArchiveCache($this->archiveHash, $file, $mimeType) ? 1 : 0;
            $this->io->progressAdvance();
        }
        $this->io->progressFinish();
        $this->io->newLine();
        $this->io->writeln(\sprintf('%d files have been pushed in cache', $counter));
    }
}
