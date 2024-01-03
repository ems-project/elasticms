<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20231222133134 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'upgrade mysql use json types';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE form_submission CHANGE data data JSON DEFAULT NULL COMMENT \'(DC2Type:json)\'');
        $this->addSql('ALTER TABLE log_message CHANGE context context JSON NOT NULL COMMENT \'(DC2Type:json)\', CHANGE extra extra JSON NOT NULL COMMENT \'(DC2Type:json)\'');
        $this->addSql('ALTER TABLE store_data CHANGE data data JSON DEFAULT NULL COMMENT \'(DC2Type:json)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE form_submission CHANGE data data JSON DEFAULT NULL COMMENT \'(DC2Type:json)\'');
        $this->addSql('ALTER TABLE log_message CHANGE context context JSON NOT NULL COMMENT \'(DC2Type:json)\', CHANGE extra extra JSON NOT NULL COMMENT \'(DC2Type:json)\'');
        $this->addSql('ALTER TABLE store_data CHANGE data data JSON DEFAULT NULL COMMENT \'(DC2Type:json)\'');
    }
}
