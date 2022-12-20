<?php

declare(strict_types=1);

namespace EMS\Release\Command;

use EMS\Release\Config;
use Github\AuthMethod;
use Github\Client as ClientGithub;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class GithubReleasePackages extends AbstractGithubCommand
{
    protected static $defaultName = 'github:release:packages';
    protected static $defaultDescription = '1) Release packages';

    private string $branch;
    private string $version;
    private string $previousVersion;
    private bool $force;

    protected function configure(): void
    {
        $this
            ->addArgument('branch', InputArgument::REQUIRED, 'branch')
            ->addArgument('version', InputArgument::REQUIRED, 'version')
            ->addArgument('previousVersion', InputArgument::REQUIRED, 'previousVersion')
            ->addOption('force', null, InputOption::VALUE_NONE, 'overwrite release')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);

        $this->branch = (string) $input->getArgument('branch');
        $this->version = (string) $input->getArgument('version');
        $this->previousVersion = (string) $input->getArgument('previousVersion');
        $this->force = true === $input->getOption('force');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title('GitHub : Release : Packages');

        $pg = $this->io->createProgressBar(\count(Config::$packages));
        $pg->start();

        $rows = [];

        foreach (Config::$packages as $name => $packageName) {
            $release = $this->getRelease($name);

            if ($release && !$this->force) {
                $rows[] = [$packageName, 'Already release', $this->getReleaseSha($name), $release['html_url']];
                $pg->advance();
                continue;
            } elseif ($release) {
                $this->deleteRelease($name, $release['id']);
                $status = 'Re-released';
            } else {
                $status = 'Fresh release';
            }

            $url = $this->createRelease($name);
            $rows[] = [$packageName, $status, $this->getReleaseSha($name), $url];

            $pg->advance();
        }

        $pg->finish();
        $this->io->newLine(2);

        $this->io->table([ 'package', 'status', 'sha', 'url'], $rows);

        return 0;
    }

    private function createRelease(string $name): string
    {
        $releaseNotes = $this->generateNotes($name);

        $release = $this->githubApi->repo()->releases()->create(self::ORG, $name, [
            'tag_name' => $this->version,
            'target_commitish' => $this->branch,
            'name' => $releaseNotes['name'],
            'body' => $releaseNotes['body'],
        ]);

        return $release['html_url'];
    }

    private function deleteRelease(string $name, int $releaseId)
    {
        $this->githubApi->repo()->releases()->remove(self::ORG, $name, $releaseId);
        $this->githubApi->git()->references()->remove(self::ORG, $name, 'tags/'.$this->version);
    }

    private function getRelease(string $name): ?array
    {
        try {
            return $this->githubApi->repo()->releases()->tag(self::ORG, $name, $this->version);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function getReleaseSha(string $name): string
    {
        $ref = $this->githubApi->git()->references()->show(self::ORG, $name, 'tags/'.$this->version);

        return $ref['object']['sha'];
    }

    private function generateNotes(string $name): array
    {
        return $this->githubApi->repo()->releases()->generateNotes(self::ORG, $name, [
            'tag_name' => $this->version,
            'target_commitish' => $this->branch,
            'previous_tag_name' => $this->previousVersion
        ]);
    }
}