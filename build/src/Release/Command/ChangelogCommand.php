<?php

declare(strict_types=1);

namespace Build\Release\Command;

use Build\Release\Version;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

class ChangelogCommand extends AbstractCommand
{
    protected static $defaultName = 'changelog';

    private Version $version;

    protected function configure(): void
    {
        $this
            ->setDescription('generate changelog')
            ->addArgument('version', InputArgument::OPTIONAL, 'version number');
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);

        $version = $input->getArgument('version');
        $version = $version ?? (string) $this->question->ask($input, $output, new Question('Version: '));
        $this->version = Version::fromTag($version);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title(\sprintf('Changelog: %s', $this->version->getTag()));

        try {
            $deploy = $this->makeDeploy($this->version, $this->getBranchForVersion());

            $io->comment(\sprintf('Previous version: %s', $deploy->previousVersion->getTag()));
            $this->io->definitionList(...$this->getChanges($deploy)->list());

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $io->error($e->getMessage());

            return self::FAILURE;
        }
    }

    private function getBranchForVersion(): string
    {
        try {
            return $this->github->getBranch($this->version->getBranchName());
        } catch (\RuntimeException) {
            return $this->github->getBranch($this->version->getBranchDev());
        }
    }
}
