<?php

declare(strict_types=1);

namespace EMS\Release\Command;

use EMS\Release\Config;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GithubReleaseDocker extends AbstractGithubRelease
{
    protected static $defaultName = 'github:release:docker';
    protected static $defaultDescription = '5) Release docker admin/web/cli';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title('GitHub : Release : Docker');

        return $this->release(Config::$docker);
    }
}