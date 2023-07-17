<?php

declare(strict_types=1);

namespace EMS\Release\Command;

use EMS\Release\Config;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProcessHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

class ComposerUpdate extends Command
{
    private const BUGFIX = 'bugfix';
    protected static $defaultName = 'composer:update';
    protected static $defaultDescription = 'Composer update admin/web/cli';

    private SymfonyStyle $io;
    private ProcessHelper $processHelper;
    private Filesystem $filesystem;
    private string $rootDir;
    private bool $bugfix;

    protected function configure(): void
    {
        $this->addOption(self::BUGFIX, null, InputOption::VALUE_NONE, 'Only update elasticms packages');
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);

        $this->bugfix = true === $input->getOption(self::BUGFIX);

        /** @var ProcessHelper $processHelper */
        $processHelper = $this->getHelper('process');
        $this->processHelper = $processHelper;

        $this->filesystem = new Filesystem();
        $this->rootDir = __DIR__.'/../../';
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title('Composer update');

        foreach (Config::APPLICATIONS as $name) {
            if ('elasticms-demo' === $name) {
                continue;
            }

            $this->io->section(\sprintf('Updating: %s', $name));
            $this->runComposerUpdate($output, $this->rootDir.$name);
            $this->io->newLine();
            $this->printEmsPackages($name);
        }

        $this->io->info('Commit composer.lock files and wait for split before releasing admin/web/cli');

        return 0;
    }

    private function printEmsPackages(string $name): void
    {
        if (!$composerLockContent = \file_get_contents($this->rootDir.$name.'/composer.lock')) {
            throw new \Exception(\sprintf('could not read composer.lock in %s', $this->rootDir.$name));
        }

        $composerLock = \json_decode($composerLockContent, true);
        $packages = $composerLock['packages'];
        $emsPackages = \array_filter($packages, fn (array $package) => \in_array($package['name'], Config::COMPOSER_PACKAGES));

        $rows = [];
        foreach ($emsPackages as $package) {
            $packageDate = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ATOM, $package['time']);

            $rows[] = [
                $package['name'],
                $package['version'],
                $package['source']['reference'],
                $packageDate ? $packageDate->format('d-m-Y H:i:s') : '',
            ];
        }

        $this->io->note($name.'/composer.lock');
        $this->io->table(['package', 'version', 'sha', 'time'], $rows);
    }

    private function runComposerUpdate(OutputInterface $output, string $directory): void
    {
        if ($this->bugfix) {
            $updateProcess = new Process(['composer', 'update', '--no-scripts', '--no-progress', '--quiet', '--', 'elasticms/*']);
        } else {
            $updateProcess = new Process(['composer', 'update', '--no-scripts', '--no-progress', '--quiet']);
        }
        $updateProcess->setWorkingDirectory($directory);

        $this->processHelper->run($output, $updateProcess);
        $this->filesystem->remove($directory.'/vendor');
    }
}
