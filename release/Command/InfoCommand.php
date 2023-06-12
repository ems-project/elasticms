<?php

declare(strict_types=1);

namespace EMS\Release\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class InfoCommand extends Command
{
    protected static $defaultName = 'info';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $style = new SymfonyStyle($input, $output);

        $style->title('EMS Release');

        $style->table(['Step', 'Description', 'Command'], [
            ['<info>Step 1</info>', 'release packages', '<comment>github:release:packages 5.2.0 5.2 5.0.1</comment>'],
            ['<info>Step 2</info>', 'check packagist', '<comment>packagist:info 5.2.0</comment>'],
            ['<info>Step 3</info>', 'update admin/web/cli', '<comment>composer:update</comment>'],
            ['<info>Step 4</info>', 'generate changelog', '<comment>github:changelog 5.2.0 5.2 5.0.1 --write</comment>'],
            ['<info>Step 5</info>', 'commit composer.lock and changelog', '<error>no command for now</error>'],
            ['<info>Step 6</info>', 'release admin/web/cli', '<comment>github:release:applications 5.2.0 5.2 5.0.1</comment>'],
            ['<info>Step 7</info>', 'release docker admin/web/cli', '<comment>github:release:docker 5.2.0 5.2 5.0.1</comment>'],
        ]);

        $style->table(['Command', 'Description'], [
            ['<comment>github:branches</comment>', 'List branches for all repositories'],
            ['<comment>list</comment>', 'List all commands'],
        ]);

        return 0;
    }
}
