<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\MariaDBPlatform;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250129140814 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Upgrade schema doctrine orm 3.3';
    }

    #[\Override]
    public function up(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof MySQLPlatform
            && !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MySQLPlatform'."
        );

        $this->addSql('ALTER TABLE form_submission CHANGE id id CHAR(36) NOT NULL, CHANGE data data JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE form_submission_file CHANGE id id CHAR(36) NOT NULL, CHANGE form_submission_id form_submission_id CHAR(36) DEFAULT NULL');
        $this->addSql('ALTER TABLE log_message CHANGE id id CHAR(36) NOT NULL, CHANGE context context JSON NOT NULL, CHANGE extra extra JSON NOT NULL');
        $this->addSql('ALTER TABLE store_data CHANGE id id CHAR(36) NOT NULL, CHANGE data data JSON DEFAULT NULL');
    }

    #[\Override]
    public function down(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof MySQLPlatform
            && !$this->connection->getDatabasePlatform() instanceof MariaDBPlatform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MySQLPlatform'."
        );

        $this->addSql('ALTER TABLE form_submission CHANGE data data JSON DEFAULT NULL COMMENT \'(DC2Type:json)\', CHANGE id id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE form_submission_file CHANGE id id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', CHANGE form_submission_id form_submission_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE log_message CHANGE context context JSON NOT NULL COMMENT \'(DC2Type:json)\', CHANGE extra extra JSON NOT NULL COMMENT \'(DC2Type:json)\', CHANGE id id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE store_data CHANGE data data JSON DEFAULT NULL COMMENT \'(DC2Type:json)\', CHANGE id id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
    }
}
