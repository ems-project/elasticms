<?php

declare(strict_types=1);

namespace EMS\Release\Command;

use EMS\Release\Config;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GithubReleaseCommand extends AbstractGithubCommand
{
    private string $type;

    private string $target;
    private string $version;
    private ?string $previousVersion = null;
    private bool $force;

    public function __construct(string $type, string $description)
    {
        parent::__construct(\sprintf('github:release:%s', $type));
        $this->setDescription($description);
        $this->type = $type;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('version', InputArgument::REQUIRED, '(new) version')
            ->addArgument('target', InputArgument::REQUIRED, 'branchName or hash')
            ->addArgument('previousVersion', InputArgument::OPTIONAL, 'previousVersion')
            ->addOption('force', null, InputOption::VALUE_NONE, 'overwrite release')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);

        $this->target = (string) $input->getArgument('target');
        $this->version = (string) $input->getArgument('version');
        $this->previousVersion = $input->getArgument('previousVersion');
        $this->force = true === $input->getOption('force');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title('GitHub : Release : '.\ucfirst($this->type));

        return $this->release(Config::REPOSITORIES[$this->type]);
    }

    /**
     * @param string[] $repositories
     */
    protected function release(array $repositories): int
    {
        $pg = $this->io->createProgressBar(\count($repositories));
        $pg->start();

        $rows = [];

        foreach ($repositories as $repository) {
            $release = $this->getRelease($repository, $this->version);

            if ($release && !$this->force) {
                $rows[] = [$repository, 'Already release', $this->getReleaseSha($repository), $release['html_url']];
                $pg->advance();
                continue;
            } elseif ($release) {
                $this->deleteRelease($repository, $release['id']);
                $status = 'Re-released';
            } else {
                $status = 'Fresh release';
            }

            $url = $this->createRelease($repository);
            $rows[] = [$repository, $status, $this->getReleaseSha($repository), $url];

            $pg->advance();
        }

        $pg->finish();
        $this->io->newLine(2);

        $this->io->table(['package', 'status', 'sha', 'url'], $rows);

        return 0;
    }

    private function createRelease(string $name): string
    {
        $releaseNotes = $this->generateNotes($name);

        $release = $this->githubApi->repo()->releases()->create(self::ORG, $name, [
            'tag_name' => $this->version,
            'target_commitish' => $this->target,
            'name' => $releaseNotes['name'],
            'body' => $releaseNotes['body'],
        ]);

        return $release['html_url'];
    }

    private function deleteRelease(string $name, int $releaseId): void
    {
        $this->githubApi->repo()->releases()->remove(self::ORG, $name, $releaseId);
        $this->githubApi->git()->references()->remove(self::ORG, $name, 'tags/'.$this->version);
    }

    /**
     * @return ?array<mixed>
     */
    private function getRelease(string $name, string $version): ?array
    {
        try {
            return $this->githubApi->repo()->releases()->tag(self::ORG, $name, $version);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function getReleaseSha(string $name): string
    {
        $ref = $this->githubApi->git()->references()->show(self::ORG, $name, 'tags/'.$this->version);

        return $ref['object']['sha'];
    }

    /**
     * @return array<mixed>
     */
    private function generateNotes(string $name): array
    {
        if ($this->previousVersion) {
            $previousRelease = $this->getRelease($name, $this->previousVersion);
        }

        $previousTagName = isset($previousRelease) ? $this->previousVersion : null;
        if (null === $previousTagName) {
            $this->io->warning(\sprintf('No previous version for "%s"', $name));
        }

        return $this->githubApi->repo()->releases()->generateNotes(self::ORG, $name, \array_filter([
            'tag_name' => $this->version,
            'target_commitish' => $this->target,
            'previous_tag_name' => $previousTagName,
        ]));
    }
}
