<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220502131453 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform,
            "Migration can only be executed safely on 'PostgreSQLPlatform'."
        );

        $this->addSql('CREATE TABLE log_message (id UUID NOT NULL, created TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, modified TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, message TEXT NOT NULL, context JSON NOT NULL, level SMALLINT NOT NULL, level_name VARCHAR(50) NOT NULL, channel VARCHAR(255) NOT NULL, extra JSON NOT NULL, formatted TEXT NOT NULL, username VARCHAR(255) DEFAULT NULL, impersonator VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN log_message.id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE form_submission ADD expire_date DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE form_submission ADD label VARCHAR(255) DEFAULT NULL');
        $this->addSql('UPDATE form_submission set label = name where label is null');
        $this->addSql('ALTER TABLE form_submission ALTER COLUMN label SET NOT NULL;');
        $this->addSql('ALTER TABLE form_submission ADD process_by VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE form_submission ALTER data DROP NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform,
            "Migration can only be executed safely on 'PostgreSQLPlatform'."
        );

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP TABLE log_message');
        $this->addSql('ALTER TABLE form_submission DROP expire_date');
        $this->addSql('ALTER TABLE form_submission DROP label');
        $this->addSql('ALTER TABLE form_submission DROP process_by');
        $this->addSql('ALTER TABLE form_submission ALTER data SET NOT NULL');
    }
}
