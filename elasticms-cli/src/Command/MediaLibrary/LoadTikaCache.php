<?php

declare(strict_types=1);

namespace App\CLI\Command\MediaLibrary;

use App\CLI\Commands;
use App\CLI\Helper\Tika\TikaHelper;
use EMS\CommonBundle\Common\Command\AbstractCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

#[AsCommand(
    name: Commands::MEDIA_LIBRARY_TIKA_CACHE,
    description: 'Generate a tika cache from files in a given folder.',
    hidden: false
)]
final class LoadTikaCache extends AbstractCommand
{
    private const string ARGUMENT_FOLDER = 'folder';
    private const string ARGUMENT_TIKA_CACHE_FOLDER = 'tika-cache-folder';
    private string $folder;
    private string $tikaCacheFolder;

    #[\Override]
    protected function configure(): void
    {
        $this
            ->addArgument(self::ARGUMENT_FOLDER, InputArgument::REQUIRED, 'Folder path')
            ->addArgument(self::ARGUMENT_TIKA_CACHE_FOLDER, InputArgument::REQUIRED, 'Folder where tika extraction can be cached')
        ;
    }

    #[\Override]
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
        $this->folder = $this->getArgumentString(self::ARGUMENT_FOLDER);
        $this->tikaCacheFolder = $this->getArgumentString(self::ARGUMENT_TIKA_CACHE_FOLDER);
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title(\sprintf('Generate Tika cache for files in %s', $this->folder));
        $finder = new Finder();
        $finder->files()->in($this->folder);
        $tikaHelper = TikaHelper::initTikaJar($this->tikaCacheFolder);

        if (!$finder->hasResults()) {
            throw new \RuntimeException('No files found!');
        }
        $this->io->comment(\sprintf('%d files located', $finder->count()));
        $progressBar = $this->io->createProgressBar($finder->count());
        foreach ($finder as $file) {
            $promise = $tikaHelper->extractFromFile($file->getPathname());
            $promise->startText();
            $promise->startMeta();
            $promise->getText();
            $promise->getMeta();
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->io->newLine();

        return self::EXECUTE_SUCCESS;
    }
}
