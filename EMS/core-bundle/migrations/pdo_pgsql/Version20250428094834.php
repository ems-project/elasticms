<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250428094834 extends AbstractMigration
{
    #[\Override]
    public function getDescription(): string
    {
        return 'Add action table';
    }

    #[\Override]
    public function up(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\PostgreSQLPlatform'."
        );

        $this->addSql(<<<'SQL'
            CREATE TABLE action (id UUID NOT NULL, status VARCHAR(25) NOT NULL, sender VARCHAR(50) NOT NULL, sender_id VARCHAR(100) NOT NULL, request JSON NOT NULL, request_hash VARCHAR(255) NOT NULL, response JSON DEFAULT NULL, created TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, created_by TEXT NOT NULL, modified TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX sender_idx ON action (sender, sender_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX request_idx ON action (request_hash)
        SQL);
    }

    #[\Override]
    public function down(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\PostgreSQLPlatform'."
        );

        $this->addSql(<<<'SQL'
            DROP TABLE action
        SQL);
    }
}
