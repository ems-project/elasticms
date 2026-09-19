<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use EMS\Helpers\Standard\Json;
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

        $sortOptions = \array_map(static fn (array $option): array => [
            'name' => $option['name'],
            'field' => $option['field'],
            'orderKey' => $option['orderkey'],
            'inverted' => $option['inverted'],
            'icon' => $option['icon'],
        ], $this->connection->fetchAllAssociative('SELECT name, field, orderkey, inverted, icon FROM sort_option ORDER BY orderkey'));
        $searchFieldOptions = \array_map(static fn (array $option): array => [
            'name' => $option['name'],
            'field' => $option['field'],
            'orderKey' => $option['orderkey'],
            'icon' => $option['icon'],
            'contentTypes' => Json::decode((string) $option['contenttypes']),
            'operators' => Json::decode((string) $option['operators']),
        ], $this->connection->fetchAllAssociative('SELECT name, field, orderkey, icon, contenttypes, operators FROM search_field_option ORDER BY orderkey'));
        $aggregateOptions = \array_map(static fn (array $option): array => [
            'name' => $option['name'],
            'config' => $option['config'],
            'template' => $option['template'],
            'orderKey' => $option['orderkey'],
            'icon' => $option['icon'],
        ], $this->connection->fetchAllAssociative('SELECT name, config, template, orderkey, icon FROM aggregate_option ORDER BY orderkey'));

        $this->addSql(<<<'SQL'
            INSERT INTO dashboard (
                id, created, modified, name, icon, label, sidebar_menu, notification_menu, definition, type, role, color, options, order_key
            ) VALUES (
                :id, NOW(), NOW(), 'advanced_search', 'fa fa-search', 'Advanced search', TRUE, FALSE, NULL, 'ems_core.dashboard.advanced_search', 'ROLE_USER', NULL, CAST(:options AS JSON), COALESCE((SELECT MAX(order_key) + 1 FROM dashboard), 1)
            )
        SQL, [
            'id' => Uuid::uuid4()->toString(),
            'options' => Json::encode([
                'sortOptions' => $sortOptions,
                'searchFieldOptions' => $searchFieldOptions,
                'aggregateOptions' => $aggregateOptions,
            ]),
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
