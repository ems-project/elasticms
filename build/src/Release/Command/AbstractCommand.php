<?php

declare(strict_types=1);

namespace Build\Release\Command;

use Build\Release\Deploy;
use Build\Release\File\Changes;
use Build\Release\Service\GithubApiService;
use Build\Release\Service\GitService;
use Build\Release\Version;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProcessHelper;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

abstract class AbstractCommand extends Command
{
    protected Filesystem $filesystem;
    protected SymfonyStyle $io;
    protected GitService $git;
    protected GithubApiService $github;
    protected ProcessHelper $processHelper;
    protected QuestionHelper $question;
    protected InputInterface $input;
    protected OutputInterface $output;

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);

        $this->io = new SymfonyStyle($input, $output);
        $this->git = new GitService();
        $this->github = new GithubApiService();
        $this->filesystem = new Filesystem();

        /** @var QuestionHelper $question */
        $question = $this->getHelper('question');
        $this->question = $question;

        /** @var ProcessHelper $process */
        $process = $this->getHelper('process');
        $this->processHelper = $process;

        $this->input = $input;
        $this->output = $output;
    }

    protected function makeDeploy(Version $version, string $branch): Deploy
    {
        $previousVersion = $this->github->getPreviousVersion($version);

        return new Deploy($version, $previousVersion, $branch);
    }

    protected function getChanges(Deploy $deploy): Changes
    {
        $releaseNotes = $this->github->getReleaseNotes($deploy);

        return new Changes($deploy->version, $releaseNotes['body']);
    }

    protected function confirm(string $question, bool $default = true): bool
    {
        $question .= ($default) ? ' (yes): ' : ' (no): ';

        return (bool) $this->question->ask($this->input, $this->output, new ConfirmationQuestion($question, $default));
    }

    protected function runProcess(Process $process): void
    {
        $this->processHelper->run($this->output, $process, null, function (string $type, string $data): void {
            if (Process::ERR === $type) {
                $this->io->warning($data);
            } else {
                $this->output->write($data);
            }
        });
    }
}
