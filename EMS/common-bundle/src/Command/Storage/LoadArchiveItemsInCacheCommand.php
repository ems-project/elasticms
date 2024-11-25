<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Command\Storage;

use EMS\CommonBundle\Commands;
use EMS\CommonBundle\Common\Command\AbstractCommand;
use EMS\CommonBundle\Storage\Archive;
use EMS\CommonBundle\Storage\StorageManager;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

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

        $archive = Archive::fromStructure($this->storageManager->getContents($this->archiveHash), $this->storageManager->getHashAlgo());
        $progressBar = $this->io->createProgressBar($archive->getCount());
        $this->storageManager->loadArchiveItemsInCache($this->archiveHash, $archive->skip($this->continue), $this->output->isQuiet() ? null : function () use ($progressBar) {
            $progressBar->advance();
        });
        $progressBar->finish();
        $this->io->newLine();
        $this->io->writeln(\sprintf('%d files have been pushed in cache', $archive->getCount()));

        return self::EXECUTE_SUCCESS;
    }
}
