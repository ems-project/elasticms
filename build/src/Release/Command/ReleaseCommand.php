<?php

declare(strict_types=1);

namespace Build\Release\Command;

use Build\Release\Config;
use Build\Release\Deploy;
use Build\Release\File\ChangelogFile;
use Build\Release\File\ComposerLockFile;
use Build\Release\Service\GithubRelease;
use Build\Release\Service\PackagistService;
use Build\Release\Version;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Process\Process;

class ReleaseCommand extends AbstractCommand
{
    protected static $defaultName = 'create';

    private Version $version;
    private PackagistService $packagist;

    protected function configure(): void
    {
        $this
            ->setDescription('release a new version')
            ->addArgument('version', InputArgument::OPTIONAL, 'version number');
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);

        $this->packagist = new PackagistService();

        $version = $input->getArgument('version');
        $version ??= (string) $this->question->ask($input, $output, new Question('Version: '));
        $this->version = Version::fromTag($version);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title(\sprintf('Release: %s', $this->version->getTag()));

        try {
            $branch = $this->github->getBranch($this->version->getBranchName());
            $deploy = $this->makeDeploy($this->version, $branch);

            $this->git->isBranch($branch)->isRemote(Config::REMOTE);

            $changes = $this->getChanges($deploy);
            $this->io->section('Changelog');
            $this->io->definitionList(...$changes->list());

            if (!$this->confirm('Start release?')) {
                return self::FAILURE;
            }

            ChangelogFile::create($deploy->version)->add($changes)->write();

            $this->io->newLine();
            $this->io->section('Release packages');
            $packageReleases = $this->release($deploy, Config::PACKAGES);

            $this->io->newLine();
            $this->io->comment('waiting 10 seconds...');
            \sleep(10);

            $this->io->section('Checking packagist');
            $this->checkPackagist(...$packageReleases);

            $this->io->section('Composer update applications');
            $this->composerUpdate($deploy, Config::APPLICATIONS, $packageReleases);

            $this->io->section('Commit release');
            $sha = $this->commitRelease($deploy);

            $this->io->section('Release mono repo');
            $this->releaseMonoRepo($deploy, $sha);

            $this->io->newLine();
            $this->io->comment('Waiting for completed split...');
            $this->github->splitsIsCompleted($sha);

            $this->io->section('Release applications');
            $this->release($deploy, Config::APPLICATIONS);

            $this->io->section('Release docker');
            $this->release($deploy, Config::DOCKER);

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->io->error($e->getMessage());

            return self::FAILURE;
        }
    }

    /**
     * @param string[] $repositories
     * @param string[] $redeployRepositories
     *
     * @return array<string, GithubRelease>
     */
    private function release(Deploy $deploy, array $repositories, array $redeployRepositories = []): array
    {
        $releases = [];

        foreach ($repositories as $repository) {
            $release = $this->github->getRelease($deploy->version, $repository);

            if (null === $release) {
                $release = $this->github->createRelease($deploy, $repository);
                $release->status = 'new';
            } elseif (\in_array($repository, $redeployRepositories, true)) {
                $this->github->deleteRelease($release);
                $release = $this->github->createRelease($deploy, $repository);
                $release->status = 'redeployed';
            } else {
                $release->status = 'existing';
            }

            $releases[$release->repository] = $release;
        }

        $table = $this->io->createTable();
        $table->setHeaders(['repository', 'release', 'sha', 'url']);
        $table->setRows(\array_map(static function (GithubRelease $release) {
            return \array_filter([$release->repository, $release->status, $release->sha, $release->url]);
        }, $releases));
        $table->render();
        $this->io->newLine();

        $existingReleases = \array_filter($releases, static fn (GithubRelease $r) => 'existing' === $r->status);
        if (0 === \count($existingReleases)) {
            return $releases;
        }

        if ($this->confirm('Redeploy existing ?', false)) {
            $redeployRepositories = \array_map(static fn (GithubRelease $r) => $r->repository, $existingReleases);

            return $this->release($deploy, $repositories, $redeployRepositories);
        }

        return $releases;
    }

    private function checkPackagist(GithubRelease ...$packageReleases): bool
    {
        \array_walk($packageReleases, fn (GithubRelease $r) => $r->packagistSha = $this->packagist->getReference($r));

        $notPublished = \array_filter($packageReleases, static fn (GithubRelease $r) => !$r->isPublished());
        if (0 === \count($notPublished)) {
            $this->io->comment('all packages published');

            return true;
        }

        $table = $this->io->createTable();
        $table->setHeaders(['repository', 'status']);
        $table->setRows(\array_map(static function (GithubRelease $release) {
            if (null === $release->packagistSha) {
                return [$release->repository, '<error>not published</error>'];
            }

            return [$release->repository, '<error>not up to date</error>'];
        }, $notPublished));
        $table->render();
        $this->io->newLine();

        if ($this->confirm('Recheck ?')) {
            return $this->checkPackagist(...$packageReleases);
        }

        throw new \RuntimeException('Packages not published');
    }

    /**
     * @param string[]                     $applications
     * @param array<string, GithubRelease> $packageReleases
     */
    private function composerUpdate(Deploy $deploy, array $applications, array $packageReleases): void
    {
        $command = 'composer update --no-scripts --no-progress --quiet';
        if ('patch' === $deploy->version->getType()) {
            $command .= ' -- elasticms/*';
        }
        $process = Process::fromShellCommandline($command);

        foreach ($applications as $application) {
            if ('elasticms-demo' === $application) {
                continue;
            }

            $this->io->comment(\sprintf('Updating: %s', $application));
            $directory = __DIR__.'/../../../../'.$application;

            $beforeLock = ComposerLockFile::create($directory);
            $process->setWorkingDirectory($directory);
            $this->processHelper->run($this->output, $process);
            $this->filesystem->remove($directory.'/vendor');
            $afterLock = ComposerLockFile::create($directory);

            $table = $this->io->createTable();
            $table->setHeaders(['repository', 'before', 'now', 'sha']);

            foreach (Config::COMPOSER_PACKAGES as $repository => $name) {
                if (null === $package = $afterLock->getPackage($name)) {
                    continue;
                }

                $release = $packageReleases[$repository] ?? null;
                if ($release?->sha !== $package->sha) {
                    throw new \RuntimeException(\sprintf('Package %s not correctly updated', $name));
                }

                $table->addRow([$name, $beforeLock->getPackage($name)?->version, $package->version, $package->sha]);
            }

            $table->render();
            $this->io->newLine();
        }
    }

    private function commitRelease(Deploy $deploy): string
    {
        $this->processHelper->run($this->output, Process::fromShellCommandline('git add .'));

        $this->runProcess(Process::fromShellCommandline('git status -s'));
        if (!$this->confirm('Commit release?')) {
            throw new \RuntimeException('Release aborted');
        }

        $this->runProcess(Process::fromShellCommandline(\sprintf('git commit -m "build: %s"', $deploy->version->getTag())));
        $this->runProcess(Process::fromShellCommandline('git push'));

        return $this->git->getLatestSha($deploy->branch);
    }

    private function releaseMonoRepo(Deploy $deploy, string $expectedSha): void
    {
        $release = $this->github->getRelease($deploy->version);

        if ($release && $this->confirm('Remove previous release?')) {
            $this->github->deleteRelease($release);
        } elseif (null === $release) {
            $release = $this->github->createRelease($deploy);
        }

        if ($release->sha !== $expectedSha) {
            throw new \RuntimeException('The mono repo not correctly released!');
        }
    }
}
