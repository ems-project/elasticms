<?php

declare(strict_types=1);

namespace Build\Release\Service;

use Build\Release\Config;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class GitService
{
    public function getLatestSha(string $branch): string
    {
        $process = Process::fromShellCommandline(\sprintf('git ls-remote %s %s | cut -f1', Config::REMOTE, $branch));

        return \trim($this->execute($process));
    }

    public function isBranch(string $expectedBranch): self
    {
        $process = Process::fromShellCommandline('git branch --show-current');
        $branch = \trim($this->execute($process));

        if ($branch !== $expectedBranch) {
            throw new \RuntimeException('The current branch is expected to be "'.$expectedBranch.'".');
        }

        return $this;
    }

    public function isRemote(string $expectedRemote): self
    {
        $process = Process::fromShellCommandline("git remote get-url $(git for-each-ref --format='%(upstream:short)' $(git symbolic-ref -q HEAD)|cut -d/ -f1)");
        $remote = \trim($this->execute($process));

        if ($remote !== $expectedRemote) {
            throw new \RuntimeException('The remote is expected to be "'.$expectedRemote.'".');
        }

        return $this;
    }

    private function execute(Process $process): string
    {
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $process->getOutput();
    }
}
