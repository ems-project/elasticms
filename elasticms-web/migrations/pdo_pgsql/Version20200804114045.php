<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;

final class Version20200804114045 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform,
            "Migration can only be executed safely on 'PostgreSQLPlatform'."
        );

        $this->addSql('CREATE SEQUENCE asset_storage_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE asset_storage (id INT NOT NULL, created TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, modified TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, hash VARCHAR(1024) NOT NULL, contents BYTEA NOT NULL, size BIGINT NOT NULL, confirmed BOOLEAN DEFAULT \'false\' NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_37945A62D1B862B8 ON asset_storage (hash)');
        $this->addSql('CREATE TABLE form_submission (id UUID NOT NULL, created TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, modified TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, name VARCHAR(255) NOT NULL, instance VARCHAR(255) NOT NULL, locale VARCHAR(2) NOT NULL, data JSON NOT NULL, process_try_counter INT DEFAULT 0 NOT NULL, process_id VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN form_submission.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN form_submission.data IS \'(DC2Type:json_array)\'');
        $this->addSql('CREATE TABLE form_submission_file (id UUID NOT NULL, form_submission_id UUID DEFAULT NULL, created TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, modified TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, file BYTEA NOT NULL, filename VARCHAR(255) NOT NULL, form_field VARCHAR(255) NOT NULL, mime_type VARCHAR(1024) NOT NULL, size BIGINT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_AEFF00A6422B0E0C ON form_submission_file (form_submission_id)');
        $this->addSql('COMMENT ON COLUMN form_submission_file.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN form_submission_file.form_submission_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE form_submission_file ADD CONSTRAINT FK_AEFF00A6422B0E0C FOREIGN KEY (form_submission_id) REFERENCES form_submission (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform,
            "Migration can only be executed safely on 'PostgreSQLPlatform'."
        );

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE form_submission_file DROP CONSTRAINT FK_AEFF00A6422B0E0C');
        $this->addSql('DROP SEQUENCE asset_storage_id_seq CASCADE');
        $this->addSql('DROP TABLE asset_storage');
        $this->addSql('DROP TABLE form_submission');
        $this->addSql('DROP TABLE form_submission_file');
    }
}
