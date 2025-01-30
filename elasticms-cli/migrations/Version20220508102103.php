<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220508102103 extends AbstractMigration
{
    #[\Override]
    public function getDescription(): string
    {
        return 'Scripts to create common bundle\'s entities (AssetStorage and LogMessage)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE asset_storage (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, created DATETIME NOT NULL, modified DATETIME NOT NULL, hash VARCHAR(1024) NOT NULL, contents BLOB NOT NULL, size BIGINT NOT NULL, confirmed BOOLEAN DEFAULT 0 NOT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_37945A62D1B862B8 ON asset_storage (hash)');
        $this->addSql('CREATE TABLE log_message (id CHAR(36) NOT NULL --(DC2Type:uuid)
        , created DATETIME NOT NULL, modified DATETIME NOT NULL, message CLOB NOT NULL, context CLOB NOT NULL --(DC2Type:json)
        , level SMALLINT NOT NULL, level_name VARCHAR(50) NOT NULL, channel VARCHAR(255) NOT NULL, extra CLOB NOT NULL --(DC2Type:json)
        , formatted CLOB NOT NULL, username VARCHAR(255) DEFAULT NULL, impersonator VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
    }

    #[\Override]
    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE asset_storage');
        $this->addSql('DROP TABLE log_message');
    }
}
