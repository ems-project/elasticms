<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Command\Local;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Mime\MimeTypes;

final class FolderUploadCommand extends AbstractLocalCommand
{
    private const string ARG_FOLDER = 'folder';

    #[\Override]
    protected function configure(): void
    {
        parent::configure();
        $this->addArgument(self::ARG_FOLDER, InputArgument::REQUIRED, 'Folder where are located the assets to upload');
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title('Local development - Upload all assets located in a folder');
        $folder = $input->getArgument(self::ARG_FOLDER);

        if (!$this->coreApi->isAuthenticated()) {
            $this->io->error(\sprintf('Not authenticated for %s, run emsch:local:login', $this->coreApi->getBaseUrl()));

            return self::EXECUTE_ERROR;
        }

        $mimeTypes = new MimeTypes();
        $finder = new Finder();
        $finder->files()->in($folder);

        if (!$finder->hasResults()) {
            $this->io->error('No file found');

            return self::EXECUTE_ERROR;
        }

        $this->io->comment(\sprintf('%d files located', $finder->count()));
        $hashesUploaded = [];
        $duplicates = [];
        $progressBar = $this->io->createProgressBar($finder->count());

        foreach ($finder as $file) {
            try {
                $realPath = $file->getRealPath();
                if (!\is_string($realPath)) {
                    throw new \RuntimeException(\sprintf('File %s not found', $file->getFilename()));
                }

                $hash = $this->coreApi->file()->uploadFile($realPath, $mimeTypes->guessMimeType($realPath));

                if (!\array_key_exists($hash, $hashesUploaded)) {
                    $hashesUploaded[$hash] = $file->getFilename();
                } else {
                    $duplicates[] = [$file->getFilename(), $hashesUploaded[$hash]];
                }
            } catch (\Throwable $e) {
                $this->io->error(\sprintf('Upload failed for "%s" (%s)', $realPath ?? $file->getFilename(), $e->getMessage()));
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->io->newLine();

        $this->io->warning(\sprintf('Found %d duplicates', \count($duplicates)));
        $this->io->table(['file', 'duplicate'], $duplicates);

        $this->io->success(\sprintf('%d (on %d) assets have been uploaded', \count($hashesUploaded), $finder->count()));

        return self::EXECUTE_SUCCESS;
    }
}
