<?php

declare(strict_types=1);

namespace EMS\Release\Command;

use Github\AuthMethod;
use Github\Client as ClientGithub;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

abstract class AbstractGithubCommand extends Command
{
    protected SymfonyStyle $io;
    protected ClientGithub $githubApi;

    protected const ORG = 'ems-project';

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
        if (null === $githubApiToken = $_SERVER['GITHUB_API_TOKEN'] ?? null) {
            throw new \RuntimeException('GITHUB_API_TOKEN not defined!');
        }
        $this->githubApi = new ClientGithub();
        $this->githubApi->authenticate($githubApiToken, AuthMethod::JWT);
    }

    protected static function orgUrl(string $name): string
    {
        return \sprintf('https://github.com/%s/%s', self::ORG, $name);
    }
}
