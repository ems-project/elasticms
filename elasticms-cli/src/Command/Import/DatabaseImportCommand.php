<?php

declare(strict_types=1);

namespace App\CLI\Command\Import;

use App\CLI\Commands;
use Doctrine\DBAL\Connection;
use EMS\CommonBundle\Common\Admin\AdminHelper;
use EMS\CommonBundle\Contracts\ExpressionServiceInterface;
use EMS\CommonBundle\Storage\StorageManager;
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

    public function __construct(
        private readonly Connection $connection,
        AdminHelper $adminHelper,
        StorageManager $storageManager,
        ExpressionServiceInterface $expressionService,
    ) {
        parent::__construct($adminHelper, $storageManager, $expressionService);
    }

    #[\Override]
    protected function configure(): void
    {
        $this->addArgument(self::ARGUMENT_TABLE, InputArgument::REQUIRED, 'Database table name.');

        parent::configure();
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

            $config = $this->getImportConfig();
            $records = $this->getRecords($this->table);

            $this->import($config, $records);

            return self::EXECUTE_SUCCESS;
        } catch (\Throwable $throwable) {
            $this->io->error($throwable->getMessage());

            return self::EXECUTE_ERROR;
        }
    }

    private function getRecords(string $table): \Generator
    {
        $sql = \sprintf('SELECT * FROM %s;', $table);
        $stmt = $this->connection->executeQuery($sql);

        while ($row = $stmt->fetchAssociative()) {
            yield $row;
        }

        return [];
    }
}
