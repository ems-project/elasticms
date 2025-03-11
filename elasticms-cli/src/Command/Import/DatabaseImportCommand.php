<?php

declare(strict_types=1);

namespace App\CLI\Command\Import;

use App\CLI\Commands;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: Commands::IMPORT_DATABASE,
    description: 'Import an database table, one document per row.',
    hidden: false
)]
final class DatabaseImportCommand extends AbstractImportCommand
{
    private const string ARGUMENT_TABLE = 'table';

    private string $table;

    #[\Override]
    protected function configure(): void
    {
        parent::configure();

        $this->addArgument(self::ARGUMENT_TABLE, InputArgument::REQUIRED, 'Database table name.');
    }

    #[\Override]
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);

        $this->table = $this->getArgumentString(self::ARGUMENT_TABLE);
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->io->title('EMS CLI - Import - Database');

            return self::EXECUTE_SUCCESS;
        } catch (\Throwable $e) {
            $this->io->error($e->getMessage());

            return self::EXECUTE_ERROR;
        }
    }
}
