<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250129140955 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Upgrade schema doctrine orm 3.3';
    }

    #[\Override]
    public function up(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\PostgreSQLPlatform'."
        );

        $this->addSql('COMMENT ON COLUMN form_submission.id IS \'\'');
        $this->addSql('COMMENT ON COLUMN form_submission.data IS \'\'');
        $this->addSql('COMMENT ON COLUMN form_submission_file.id IS \'\'');
        $this->addSql('COMMENT ON COLUMN form_submission_file.form_submission_id IS \'\'');
        $this->addSql('COMMENT ON COLUMN log_message.id IS \'\'');
        $this->addSql('COMMENT ON COLUMN store_data.id IS \'\'');
    }

    #[\Override]
    public function down(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\PostgreSQLPlatform'."
        );

        $this->addSql('COMMENT ON COLUMN form_submission.data IS \'(DC2Type:json)\'');
        $this->addSql('COMMENT ON COLUMN form_submission.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN store_data.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN log_message.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN form_submission_file.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN form_submission_file.form_submission_id IS \'(DC2Type:uuid)\'');
    }
}
