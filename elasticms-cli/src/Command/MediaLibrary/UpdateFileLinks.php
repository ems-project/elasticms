<?php

declare(strict_types=1);

namespace App\CLI\Command\MediaLibrary;

use App\CLI\Commands;
use EMS\CommonBundle\Common\Command\AbstractCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: Commands::UPDATE_FILE_LINKS,
    description: '', // TODO Ajouter une description au script
    hidden: false
)]
final class UpdateFileLinks extends AbstractCommand
{
    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title(\sprintf('Convert ems file links into ems object link in %s', 'content type name'));

        
        return self::EXECUTE_SUCCESS;
    }
}
