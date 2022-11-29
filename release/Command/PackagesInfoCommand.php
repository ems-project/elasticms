<?php

declare(strict_types=1);

namespace EMS\Release\Command;

use EMS\Release\Config;
use Packagist\Api\Client as ClientPackagist;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class PackagesInfoCommand extends Command
{
    protected static $defaultName = 'packages:info';

    private SymfonyStyle $io;
    private ClientPackagist $packagistApi;

    private string $version;

    protected function configure(): void
    {
        $this
            ->addArgument('version', InputArgument::REQUIRED, 'version')
            ->setDescription('Show packagist information')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
        $this->version = (string) $input->getArgument('version');
        $this->packagistApi = new ClientPackagist();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title('Packages info');

        $pg = $this->io->createProgressBar(\count(Config::$packages));
        $pg->start();

        $rows = [];

        foreach (Config::$packages as $packageName) {
            $package = $this->packagistApi->getComposerReleases($packageName)[$packageName];
            $versions = $package->getVersions();

            $version = $versions[$this->version] ?? null;
            $dist = $version ? $version->getDist() : null;

            $rows[] = [ $packageName, $version ? $version->getVersion() : 'X', ($dist ? $dist->getReference() : 'X')];
            $pg->advance();
        }

        $pg->finish();
        $this->io->newLine(2);

        $this->io->table([ 'package', 'version', 'sha'], $rows);

        return 0;
    }
}