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

class PackagistInfo extends Command
{
    protected static $defaultName = 'packagist:info';
    protected static $defaultDescription = 'Check if packages are published';

    private SymfonyStyle $io;
    private ClientPackagist $packagistApi;

    private string $version;

    protected function configure(): void
    {
        $this
            ->addArgument('version', InputArgument::REQUIRED, 'version')
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
        $this->io->title('Packagist info');

        $pg = $this->io->createProgressBar(\count(Config::PACKAGES));
        $pg->start();

        $rows = [];

        foreach (Config::PACKAGES as $repository) {
            $packageName = Config::COMPOSER_PACKAGES[$repository];
            $package = $this->packagistApi->getComposerReleases($packageName)[$packageName];
            $versions = $package->getVersions();

            $version = $versions[$this->version] ?? null;
            $dist = $version?->getDist();

            $rows[] = [$packageName, $version ? $version->getVersion() : 'X', $dist ? $dist->getReference() : 'X'];
            $pg->advance();
        }

        $pg->finish();
        $this->io->newLine(2);

        $this->io->table(['package', 'version', 'sha'], $rows);

        return 0;
    }
}
