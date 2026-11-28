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
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Process\Process;

#[AsCommand(
    name: 'create',
    description: 'Release a new version',
    hidden: false
)]
class ReleaseCommand extends AbstractCommand
{
    private Version $version;
    private PackagistService $packagist;

    protected function configure(): void
    {
        $this->addArgument('version', InputArgument::OPTIONAL, 'version number');
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

            $this->git->isBranch($branch)->isRemote(Config::REMOTE, Config::REMOTE_SSH);

            $this->io->section('Validating composer requirements');
            $this->ensureComposerRequirements($branch);

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
            $this->composerUpdate($deploy, $packageReleases);

            $this->io->section('Commit release');
            $sha = $this->commit(\sprintf('build: %s', $this->version->getTag()), $branch);

            $this->io->section('Release mono repo');
            $this->releaseMonoRepo($deploy, $sha);

            $this->io->comment('Waiting for completed split...');
            $this->github->splitsIsCompleted($sha);

            $this->io->section('Release applications');
            $this->release($deploy, Config::APPLICATIONS);

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->io->error($e->getMessage());

            return self::FAILURE;
        }
    }

    private function ensureComposerRequirements(string $branch): void
    {
        $issues = $this->validateComposerRequirements($this->version);

        if ([] === $issues) {
            $this->io->comment('All composer requirements are correct.');

            return;
        }

        $table = $this->io->createTable();
        $table->setHeaders(['file', 'package', 'expected', 'actual']);
        $table->setRows(\array_map(static fn (array $i) => [$i['file'], $i['package'], $i['expected'], $i['actual']], $issues));
        $table->render();

        $this->fixComposerRequirements($issues);
        $this->io->comment(\sprintf('Fixed %d requirement(s).', \count($issues)));

        $requireConstraint = \sprintf('%d.%d.*', $this->version->major, $this->version->minor);
        $sha = $this->commit(\sprintf('release: require %s', $requireConstraint), $branch);

        $this->io->comment('Waiting for completed split after requirements fix...');
        $this->github->splitsIsCompleted($sha);
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
     * @param array<string, GithubRelease> $packageReleases
     */
    private function composerUpdate(Deploy $deploy, array $packageReleases): void
    {
        $command = 'composer update --no-scripts --no-progress --with-dependencies --quiet';
        if ('patch' === $deploy->version->getType()) {
            $command .= ' -- elasticms/*';
        }
        $process = Process::fromShellCommandline($command);

        foreach (Config::APPLICATIONS as $application) {
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

    private function commit(string $message, string $branch): string
    {
        $this->runProcess(Process::fromShellCommandline('git add .'));
        $this->runProcess(Process::fromShellCommandline('git status -s'));
        if (!$this->confirm(\sprintf('Commit "%s"?', $message))) {
            throw new \RuntimeException('Release aborted');
        }

        $this->runProcess(Process::fromShellCommandline(\sprintf('git commit -m "%s"', $message)));
        $this->runProcess(Process::fromShellCommandline('git push'));

        return $this->git->getLatestSha($branch);
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

    /**
     * @return array<array{file: string, fullPath: string, package: string, expected: string, actual: string}>
     */
    private function validateComposerRequirements(Version $version): array
    {
        $expected = \sprintf('%d.%d.*', $version->major, $version->minor);
        $root = \dirname(__DIR__, 4);
        $issues = [];

        $composerFiles = [];
        foreach (Config::COMPOSER_PACKAGES as $composerPackage) {
            $packageDir = \substr($composerPackage, \strlen('elasticms/'));
            $composerFiles[] = $root.'/EMS/'.$packageDir.'/composer.json';
        }
        foreach (['elasticms-admin', 'elasticms-web', 'elasticms-cli'] as $app) {
            $composerFiles[] = $root.'/'.$app.'/composer.json';
        }

        foreach ($composerFiles as $file) {
            if (!\file_exists($file)) {
                continue;
            }

            /** @var array{require?: array<string, string>} $data */
            $data = \json_decode((string) \file_get_contents($file), true);
            foreach ($data['require'] ?? [] as $package => $constraint) {
                if (!\str_starts_with($package, 'elasticms/')) {
                    continue;
                }
                if ($constraint !== $expected) {
                    $issues[] = [
                        'file' => \str_replace($root.'/', '', $file),
                        'fullPath' => $file,
                        'package' => $package,
                        'expected' => $expected,
                        'actual' => $constraint,
                    ];
                }
            }
        }

        return $issues;
    }

    /**
     * @param array<array{file: string, fullPath: string, package: string, expected: string, actual: string}> $issues
     */
    private function fixComposerRequirements(array $issues): void
    {
        $byFile = [];
        foreach ($issues as $issue) {
            $byFile[$issue['fullPath']][$issue['package']] = $issue['expected'];
        }

        foreach ($byFile as $fullPath => $packages) {
            $content = (string) \file_get_contents($fullPath);
            foreach ($packages as $package => $expected) {
                $content = (string) \preg_replace(
                    '/"'.\preg_quote($package, '/').'"\s*:\s*"[^"]*"/',
                    \sprintf('"%s": "%s"', $package, $expected),
                    $content
                );
            }
            \file_put_contents($fullPath, $content);
        }
    }
}
