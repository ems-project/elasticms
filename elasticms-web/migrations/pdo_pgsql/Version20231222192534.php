<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20231222192534 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform,
            "Migration can only be executed safely on 'PostgreSQLPlatform'."
        );

        $this->addSql('CREATE TABLE store_data (id UUID NOT NULL, key VARCHAR(2048) NOT NULL, data JSON DEFAULT NULL, created TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, modified TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4F4A5DAD8A90ABA9 ON store_data (key)');
        $this->addSql('COMMENT ON COLUMN store_data.id IS \'(DC2Type:uuid)\'');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform,
            "Migration can only be executed safely on 'PostgreSQLPlatform'."
        );

        $this->addSql('CREATE SCHEMA schema_skeleton_adm');
        $this->addSql('DROP TABLE store_data');
    }
}
