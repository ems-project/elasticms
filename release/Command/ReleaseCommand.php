<?php

declare(strict_types=1);

namespace EMS\Release\Command;

use Github\AuthMethod;
use Github\Client as ClientGithub;
use Packagist\Api\Client as ClientPackagist;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ReleaseCommand extends Command
{
    protected static $defaultName = 'release';

    private SymfonyStyle $io;
    private ClientGithub $githubApi;
    private ClientPackagist $packagistApi;

    private string $branch;
    private string $version;
    private string $previousVersion;

    private static string $organization = 'ems-project';
    private static array $packages = [
        'EMSClientHelperBundle' => 'elasticms/client-helper-bundle',
        'EMSCommonBundle' => 'elasticms/common-bundle',
        'EMSCoreBundle' => 'elasticms/core-bundle',
        'EMSFormBundle' => 'elasticms/form-bundle',
        'EMSSubmissionBundle' => 'elasticms/submission-bundle',
        'helpers' => 'elasticms/helpers',
        'xliff' => 'elasticms/xliff'
    ];

    protected function configure(): void
    {
        $this
            ->addArgument('branch', InputArgument::REQUIRED, 'branch')
            ->addArgument('version', InputArgument::REQUIRED, 'version')
            ->addArgument('previousVersion', InputArgument::REQUIRED, 'previousVersion')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
        if (null === $githubApiToken = $_SERVER['GITHUB_API_TOKEN'] ?? null) {
            throw new \RuntimeException('GITHUB_API_TOKEN not defined!');
        }
        $this->githubApi = new ClientGithub();
        $this->githubApi->authenticate($githubApiToken, AuthMethod::JWT);

        $this->packagistApi = new ClientPackagist();

        $this->branch = (string) $input->getArgument('branch');
        $this->version = (string) $input->getArgument('version');
        $this->previousVersion = (string) $input->getArgument('previousVersion');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title(vsprintf('Release %s from %s branch (previous %s)', [
            $this->version,
            $this->branch,
            $this->previousVersion
        ]));

        $this->io->section('Release packages');
        $this->releasePackages();

        $this->io->section('Checking packagist');
        $this->checkPackagist();

        return 1;
    }

    private function releasePackages()
    {
        foreach (self::$packages as $name => $packageName) {
            if ($release = $this->getRelease($name)) {
                $this->io->writeln(sprintf('- %s: %s already exists (%s)', $packageName, $this->version, $release['html_url']));
                continue;
            }

            try {
                $releaseNotes = $this->githubApi->repo()->releases()->generateNotes(self::$organization, $name, [
                    'tag_name' => $this->version,
                    'target_commitish' => $this->branch,
                    'previous_tag_name' => $this->previousVersion
                ]);
                $newRelease = $this->githubApi->repo()->releases()->create(self::$organization, $name, [
                    'tag_name' => $this->version,
                    'target_commitish' => $this->branch,
                    'name' => $releaseNotes['name'],
                    'body' => $releaseNotes['body'],
                ]);
                $url = $newRelease['html_url'];
                $this->io->writeln(sprintf('- %s: %s released (%s)', $packageName, $this->version, $url));
            } catch (\Throwable $e) {
                $this->io->error(sprintf('- %s: %s (https://github.com/ems-project/%s)', $packageName, $e->getMessage(), $name));
            }
        }
    }

    private function getRelease(string $name): ?array
    {
        try {
            return $this->githubApi->repo()->releases()->tag(self::$organization, $name, $this->version);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function checkPackagist(): bool
    {
        foreach (self::$packages as $packageName) {
            $package = $this->packagistApi->getComposerReleases($packageName)[$packageName];

            if (null === ($package->getVersions()[$this->version] ?? null)) {
                $this->io->warning(sprintf('"%s" not published on packagist for "%s"', $packageName, $this->version));
                return false;
            }

            $this->io->writeln(sprintf('- %s: %s published', $packageName, $this->version));
        }

        return true;
    }
}