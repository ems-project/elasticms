<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Command\Website;

use EMS\CommonBundle\Commands;
use EMS\CommonBundle\Common\Command\AbstractCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Finder\Finder;

#[AsCommand(
    name: Commands::WEBSITE_REWRITE_URLS,
    description: 'Rewrite URLs in a static website and export the result to a target directory.',
    hidden: false
)]
class RewriteUrlsCommand extends AbstractCommand
{
    public const ARGUMENT_SOURCE = 'source';
    public const ARGUMENT_TARGET = 'target';
    public const OPTION_BASE_URLS = 'base-urls';
    private string $source;
    private string $target;
    /** @var string[] */
    private array $baseUrls;
    private Filesystem $filesystem;

    #[\Override]
    protected function configure(): void
    {
        parent::configure();
        $this
            ->addArgument(self::ARGUMENT_SOURCE, InputArgument::REQUIRED, 'Source directory of the static website')
            ->addArgument(self::ARGUMENT_TARGET, InputArgument::REQUIRED, 'Target directory where the rewritten website is exported')
            ->addOption(self::OPTION_BASE_URLS, null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Base URLs to rewrite (can be used multiple times)')
        ;
    }

    #[\Override]
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
        $this->source = $this->getArgumentString(self::ARGUMENT_SOURCE);
        $this->target = $this->getArgumentString(self::ARGUMENT_TARGET);
        $this->baseUrls = $this->getOptionStringArray(self::OPTION_BASE_URLS, false);
        $this->filesystem = new Filesystem();
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title('EMS - Website - Rewrite URLs');

        if (!\is_dir($this->source)) {
            $this->io->error(\sprintf('The source "%s" is not a directory.', $this->source));

            return self::EXECUTE_ERROR;
        }

        if (!$this->prepareTarget()) {
            return self::EXECUTE_ERROR;
        }

        $finder = new Finder();
        $finder->files()->in($this->source)->ignoreDotFiles(false)->ignoreVCS(false);

        $this->io->progressStart($finder->count());
        foreach ($finder as $file) {
            switch ($file->getExtension()) {
                default:
                    $this->filesystem->copy(
                        $file->getPathname(),
                        Path::join($this->target, $file->getRelativePathname()),
                        true
                    );
            }
            $this->io->progressAdvance();
        }
        $this->io->progressFinish();
        $this->io->newLine(2);

        return self::EXECUTE_SUCCESS;
    }

    private function prepareTarget(): bool
    {
        if (!$this->filesystem->exists($this->target)) {
            if (!$this->io->confirm(\sprintf('The target directory "%s" does not exist, do you want to create it?', $this->target), false)) {
                $this->io->warning('Target directory not created, aborting.');

                return false;
            }
            $this->filesystem->mkdir($this->target);

            return true;
        }

        if (!\is_dir($this->target)) {
            $this->io->error(\sprintf('The target "%s" is not a directory.', $this->target));

            return false;
        }

        $finder = new Finder();
        $finder->in($this->target)->ignoreDotFiles(false)->ignoreVCS(false)->depth('== 0');
        if (!$finder->hasResults()) {
            return true;
        }

        if (!$this->io->confirm(\sprintf('The target directory "%s" is not empty, do you want to delete its content?', $this->target), false)) {
            $this->io->warning('Target directory not emptied, aborting.');

            return false;
        }
        $this->filesystem->remove($finder);

        return true;
    }
}
