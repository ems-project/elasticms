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

class PackagesReleaseCommand extends Command
{
    protected static $defaultName = 'packages:release';

    private SymfonyStyle $io;
    private ClientGithub $githubApi;

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
            ->setDescription('Release packages on Github')
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

        $this->branch = (string) $input->getArgument('branch');
        $this->version = (string) $input->getArgument('version');
        $this->previousVersion = (string) $input->getArgument('previousVersion');
        $this->force = true === $input->getOption('force');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title('Packages release');

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

        $release = $this->githubApi->repo()->releases()->create(Config::$organization, $name, [
            'tag_name' => $this->version,
            'target_commitish' => $this->branch,
            'name' => $releaseNotes['name'],
            'body' => $releaseNotes['body'],
        ]);

        return $release['html_url'];
    }

    private function deleteRelease(string $name, int $releaseId)
    {
        $this->githubApi->repo()->releases()->remove(Config::$organization, $name, $releaseId);
        $this->githubApi->git()->references()->remove(Config::$organization, $name, 'tags/'.$this->version);
    }

    private function getRelease(string $name): ?array
    {
        try {
            return $this->githubApi->repo()->releases()->tag(Config::$organization, $name, $this->version);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function getReleaseSha(string $name): string
    {
        $ref = $this->githubApi->git()->references()->show(Config::$organization, $name, 'tags/'.$this->version);

        return $ref['object']['sha'];
    }

    private function generateNotes(string $name): array
    {
        return $this->githubApi->repo()->releases()->generateNotes(Config::$organization, $name, [
            'tag_name' => $this->version,
            'target_commitish' => $this->branch,
            'previous_tag_name' => $this->previousVersion
        ]);
    }
}