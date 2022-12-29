<?php

declare(strict_types=1);

namespace EMS\Release\Command;

use EMS\Release\Config;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use function Symfony\Component\String\u;

class GithubChangeLogCommand extends AbstractGithubCommand
{
    protected static $defaultName = 'github:changelog';

    private string $target;
    private string $version;
    private int $versionNumber;
    private ?string $previousVersion = null;
    private string $file;
    private bool $write;
    private \DateTimeInterface $date;

    protected function configure(): void
    {
        $this
            ->addArgument('version', InputArgument::REQUIRED, '(new) version')
            ->addArgument('target', InputArgument::REQUIRED, 'branchName or hash')
            ->addArgument('previousVersion', InputArgument::OPTIONAL, 'previousVersion')
            ->addOption('date', null, InputOption::VALUE_OPTIONAL, 'date (Y-m-d)')
            ->addOption('write', null, InputOption::VALUE_NONE)
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);

        $this->version = (string) $input->getArgument('version');
        $this->versionNumber = (int) u($this->version)->slice(0, 1)->toString();
        $this->target = (string) $input->getArgument('target');
        $this->previousVersion = $input->getArgument('previousVersion');

        $this->file = \sprintf(__DIR__.'/../../CHANGELOG-%d.x.md', $this->versionNumber);
        $this->write = true === $input->getOption('write');

        $inputDate = $input->getOption('date') ?? 'now';
        $this->date = new \DateTimeImmutable($inputDate);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $releaseNotes = $this->githubApi->repo()->releases()->generateNotes(self::ORG, 'elasticms', \array_filter([
            'tag_name' => $this->version,
            'target_commitish' => $this->target,
            'previous_tag_name' => $this->previousVersion,
        ]));

        $changeLog = $this->createChangeLog($releaseNotes['body']);

        $list = [
            \sprintf('<info>%s</info>', $this->getTitle()),
        ];
        foreach ($changeLog as $type => $pullRequests) {
            $list[] = \sprintf('<comment># %s</comment>', Config::PULL_REQUESTS[$type]);
            $list = [...$list, ...$pullRequests];
        }
        $this->io->definitionList(...$list);

        if ($this->write) {
            $this->writeChangeLog($changeLog);
            $this->io->note(\sprintf('Written changelog in "%s"', \basename($this->file)));
        }

        return 0;
    }

    private function getTitle(): string
    {
        return \sprintf('## %s (%s)', $this->version, $this->date->format('Y-m-d'));
    }

    /**
     * @param array<string, string[]> $changelog
     */
    private function writeChangeLog(array $changelog): void
    {
        if (!\file_exists($this->file)) {
            \touch($this->file);
        }

        $write = $this->getTitle().\PHP_EOL;

        foreach ($changelog as $type => $pullRequests) {
            $write .= \sprintf('### %s', Config::PULL_REQUESTS[$type]).\PHP_EOL;
            foreach ($pullRequests as $pullRequest) {
                $write .= $pullRequest.\PHP_EOL;
            }
        }

        if (false === $content = \file_get_contents($this->file)) {
            throw new \Exception(\sprintf('Could not read %s', $this->file));
        }

        $header = \sprintf('# Changelog %d.x', $this->versionNumber).\PHP_EOL;
        $content = \str_replace($header, '', $content);

        \file_put_contents($this->file, $header.\PHP_EOL.$write.$content);
    }

    /**
     * @return array<string, string[]>
     */
    private function createChangeLog(string $releaseNotes): array
    {
        $types = \array_keys(Config::PULL_REQUESTS);
        $regex = \sprintf('/^\*.(?<type>%s).*/s', \implode('|', $types));

        $pullRequests = \array_map(fn (string $title) => [], Config::PULL_REQUESTS);
        $line = \strtok($releaseNotes, \PHP_EOL);

        while (false !== $line) {
            if (\preg_match($regex, $line, $matches)) {
                $pullRequests[$matches['type']][] = $line;
            }
            $line = \strtok(\PHP_EOL);
        }

        $changeLog = \array_filter($pullRequests);
        \array_walk($changeLog, fn (array &$pullRequest) => \sort($pullRequest));

        return $changeLog;
    }
}
