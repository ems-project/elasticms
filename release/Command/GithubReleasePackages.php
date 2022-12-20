<?php

declare(strict_types=1);

namespace EMS\Release\Command;

use EMS\Release\Config;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GithubReleasePackages extends AbstractGithubRelease
{
    protected static $defaultName = 'github:release:packages';
    protected static $defaultDescription = '1) Release packages';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title('GitHub : Release : Packages');

        return $this->release(Config::$packages);
    }
}