<?php

declare(strict_types=1);

namespace EMS\Release\Command;

use EMS\Release\Config;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProcessHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

class ComposerUpdate extends Command
{
    protected static $defaultName = 'composer:update';
    protected static $defaultDescription = '3) Composer update admin/web/cli';

    private SymfonyStyle $io;
    private ProcessHelper $processHelper;
    private Filesystem $filesystem;
    private string $workingDir;

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
        $this->processHelper = $this->getHelper('process');
        $this->filesystem = new Filesystem();
        $this->workingDir = __DIR__.'/../../';
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title('Composer update');

        foreach (\array_keys(Config::$applications) as $name) {
            $this->io->section(sprintf('Updating: %s', $name));
            $this->runComposerUpdate($output, $this->workingDir . $name);
            $this->io->newLine();
        }

        foreach (\array_keys(Config::$applications) as $name) {
            $this->printEmsPackages($name);
        }

        return 0;
    }

    private function printEmsPackages(string $name)
    {
        $lockFile = \json_decode(file_get_contents($this->workingDir . $name . '/composer.lock'), true);
        $packages = $lockFile['packages'];
        $emsPackages = \array_filter($packages, fn (array $package) => \in_array($package['name'], Config::$packages));

        $rows = array_map(fn (array $package) => [
            $package['name'],
            $package['version'],
            $package['source']['reference'],
            \DateTimeImmutable::createFromFormat(\DateTimeInterface::ATOM, $package['time'])->format('d-m-Y H:i:s')
        ], $emsPackages);

        $this->io->note($name . '/composer.lock');
        $this->io->table([ 'package', 'version', 'sha', 'time'], $rows);
    }

    private function runComposerUpdate(OutputInterface $output, string $directory): void
    {
        $updateProcess = new Process(['composer', 'update', '--no-scripts', '--no-progress', '--quiet']);
        $updateProcess->setWorkingDirectory($directory);

        $this->processHelper->run($output, $updateProcess);
        $this->filesystem->remove($directory.'/vendor');
    }
}