<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Command;

use EMS\CommonBundle\Common\Command\AbstractCommand;
use EMS\CommonBundle\Storage\StorageManager;
use EMS\CoreBundle\Commands;
use EMS\CoreBundle\Service\AssetExtractorService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

#[AsCommand(
    name: Commands::ASSET_EXTRACT,
    description: "Extracts data from all found files and loads it into the asset extractor service's cache",
    aliases: ['ems:asset:extract'],
    hidden: false
)]
class ExtractAssetCommand extends AbstractCommand
{
    private const string ARG_PATH = 'path';
    private const string ARG_NAME = 'name';

    public function __construct(
        protected LoggerInterface $logger,
        protected AssetExtractorService $extractorService,
        protected StorageManager $storageManager
    ) {
        parent::__construct();
    }

    #[\Override]
    protected function configure(): void
    {
        $this
            ->addArgument(self::ARG_PATH, InputArgument::REQUIRED, 'Path to the files to extract data from')
            ->addArgument(self::ARG_NAME, InputArgument::OPTIONAL, 'File pattern or file name i.e. *.pdf', '*.*')
        ;
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $path = $this->getArgumentString(self::ARG_PATH);
        $name = $this->getArgumentString(self::ARG_NAME);

        $files = new Finder()->in($path)->name($name);

        $progress = $this->io->createProgressBar($files->count());
        $progress->start();

        foreach ($files as $file) {
            if (false === $realPath = $file->getRealPath()) {
                $progress->advance();
                continue;
            }

            $hash = $this->storageManager->computeFileHash($realPath);
            $this->extractorService->extractMetaData($hash, $realPath);

            $progress->advance();
        }
        $progress->finish();

        return self::EXECUTE_SUCCESS;
    }
}
