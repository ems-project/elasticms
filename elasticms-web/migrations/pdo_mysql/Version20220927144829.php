<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220927144829 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof MySQLPlatform,
            "Migration can only be executed safely on 'MySQLPlatform'."
        );

        $this->addSql('ALTER TABLE log_message ADD ouuid VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE form_submission CHANGE data data LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\'');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof MySQLPlatform,
            "Migration can only be executed safely on 'MySQLPlatform'."
        );

        $this->addSql('ALTER TABLE log_message DROP ouuid');
        $this->addSql('ALTER TABLE form_submission CHANGE data data LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json_array)\'');
    }
}
