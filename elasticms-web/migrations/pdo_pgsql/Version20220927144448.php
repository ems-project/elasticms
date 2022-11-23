<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220927144448 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform,
            "Migration can only be executed safely on 'PostgreSQLPlatform'."
        );

        $this->addSql('ALTER TABLE log_message ADD ouuid VARCHAR(255) DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN form_submission.data IS \'(DC2Type:json)\'');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform,
            "Migration can only be executed safely on 'PostgreSQLPlatform'."
        );

        $this->addSql('ALTER TABLE log_message DROP ouuid');
        $this->addSql('COMMENT ON COLUMN form_submission.data IS \'(DC2Type:json_array)\'');
    }
}
