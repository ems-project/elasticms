<?php

declare(strict_types=1);

namespace EMS\Release\Command;

use EMS\Release\Config;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GithubReleaseApplications extends AbstractGithubRelease
{
    protected static $defaultName = 'github:release:applications';
    protected static $defaultDescription = '4) Release applications admin/web/cli';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title('GitHub : Release : Applications');

        return $this->release(Config::$applications);
    }
}