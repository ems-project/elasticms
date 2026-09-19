<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Ramsey\Uuid\Uuid;

final class Version20260919133300 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'If needed, create an advanced search dashboard';
    }

    #[\Override]
    public function up(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\PostgreSQLPlatform'."
        );

        $hasAdvancedSearchOptions = (bool) $this->connection->fetchOne(<<<'SQL'
            SELECT EXISTS (
                SELECT 1
                FROM content_type
                WHERE roles IS NOT NULL
                  AND COALESCE(roles::jsonb ->> 'show_link_search', 'not-defined') <> 'not-defined'
            )
            OR EXISTS (SELECT 1 FROM sort_option)
            OR EXISTS (SELECT 1 FROM search_field_option)
            OR EXISTS (SELECT 1 FROM aggregate_option)
        SQL);

        if (!$hasAdvancedSearchOptions || $this->connection->fetchOne(
            'SELECT 1 FROM dashboard WHERE name = :name',
            ['name' => 'advanced_search']
        )) {
            return;
        }

        $this->addSql(<<<'SQL'
            INSERT INTO dashboard (
                id, created, modified, name, icon, label, sidebar_menu, notification_menu, definition, type, role, color, options, order_key
            ) VALUES (
                :id, NOW(), NOW(), 'advanced_search', 'fa fa-search', 'Advanced search', TRUE, FALSE, NULL, 'ems_core.dashboard.advanced_search', 'ROLE_USER', NULL, '{}'::json, COALESCE((SELECT MAX(order_key) + 1 FROM dashboard), 1)
            )
        SQL, [
            'id' => Uuid::uuid4()->toString(),
        ]);
    }

    #[\Override]
    public function down(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\PostgreSQLPlatform'."
        );

        $this->addSql('DELETE FROM dashboard WHERE name = :name', [
            'name' => 'advanced_search',
        ]);
    }
}
