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
        $connection = $this->connection;
        $views = \array_map(static fn (array $view): array => [
            'id' => $view['id'],
            'environments' => $view['environments'],
            'contenttypes' => $view['contenttypes'],
            'sort_by' => $view['sort_by'],
            'sort_order' => $view['sort_order'],
            'default_search' => $view['default_search'],
            'minimum_should_match' => $view['minimum_should_match'] ?? 1,
            'contentTypeId' => $view['content_type_id'],
            'filters' => \array_map(static fn (array $filter): array => [
                'booleanClause' => $filter['boolean_clause'],
                'field' => $filter['field'],
                'operator' => $filter['operator'],
                'pattern' => $filter['pattern'],
                'boost' => $filter['boost'],
            ], $connection->fetchAllAssociative('SELECT boolean_clause, field, operator, pattern, boost FROM search_filter WHERE search_id = :id', ['id' => $view['id']])),
        ], $this->connection->fetchAllAssociative('SELECT id, environments, contenttypes, sort_by, sort_order, default_search, minimum_should_match, content_type_id FROM search'));

        $environments = $this->connection->fetchFirstColumn('SELECT name FROM environment WHERE in_default_search IS TRUE ORDER BY order_key');
        if ([] === $environments) {
            $environments = $this->connection->fetchFirstColumn('SELECT name FROM environment ORDER BY order_key');
        }
        $contentTypes = [];
        $sortBy = null;
        $sortOrder = null;
        $filters = [[
            'booleanClause' => 'must',
            'field' => '',
            'operator' => 'must_et',
            'pattern' => '',
            'boost' => '',
            'minimum_should_match' => 1,
        ]];
        $minimumShouldMatch = 1;
        foreach ($views as $view) {
            if ($view['default_search']) {
                $environments = Json::decode($view['environments']);
                $contentTypes = Json::decode($view['contenttypes']);
                $sortBy = $view['sort_by'];
                $sortOrder = $view['sort_order'];
                $filters = $view['filters'];
                $minimumShouldMatch = $view['minimum_should_match'];
            }
            if ($view['contentTypeId']) {
                $contentType = $connection->fetchAssociative('SELECT name, pluralname, singularname FROM content_type WHERE id = :id', ['id' => $view['contentTypeId']]);
                if (!$contentType) {
                    continue;
                }

                $this->addSql(<<<'SQL'
                    INSERT INTO view (
                        id, content_type_id, created, modified, name, type, icon, label, role, public, options, order_key, definition
                    ) VALUES (
                        nextval('view_id_seq'), :contentTypeId, NOW(), NOW(), :name, 'ems.view.redirection', 'fa fa-search', :label,
                        'ROLE_USER', FALSE, CAST(:options AS JSON), COALESCE((SELECT MAX(order_key) + 1 FROM dashboard), 1), NULL
                    )
                SQL, [
                            'contentTypeId' => $view['contentTypeId'],
                            'name' => \sprintf('search_in_%s', $contentType['name']),
                            'label' => \sprintf('Search in %s', $contentType['pluralname']),
                            'options' => Json::encode([
                                'template' => \sprintf(<<<'TWIG'
                                {%%- set data = {contentTypes:[view.contentType.name],environments:[view.contentType.environment.name],filters:%s,minimumShouldMatch:"%d",sortBy:"%s",sortOrder:"%s"} -%%}
                                {%%- set uid = emsco_save_contents(data|json_encode, 'search_%s.json', 'application/json', 1).sha1 -%%}
                                
                                {{- path('emsco_dashboard', {uid:uid, name:'advanced_search'}) -}}
                                TWIG, Json::encode($view['filters']), $view['minimum_should_match'], $view['sort_by'], $view['sort_order'], $contentType['name']),
                            ]),
                        ]);

            }
        }

        $this->addSql(<<<'SQL'
            INSERT INTO dashboard (
                id, created, modified, name, icon, label, sidebar_menu, notification_menu, definition, type, role, color, options, order_key
            ) VALUES (
                :id, NOW(), NOW(), 'advanced_search', 'fa fa-search', 'Search', TRUE, FALSE,
                CASE WHEN EXISTS (SELECT 1 FROM dashboard WHERE definition = 'quick_search') THEN NULL ELSE 'quick_search' END,
                'ems_core.dashboard.advanced_search', 'ROLE_USER', NULL, CAST(:options AS JSON), COALESCE((SELECT MAX(order_key) + 1 FROM dashboard), 1)
            )
        SQL, [
            'id' => Uuid::uuid4()->toString(),
            'options' => Json::encode([
                'environments' => $environments,
                'contentTypes' => $contentTypes,
                'sortBy' => $sortBy,
                'sortOrder' => $sortOrder,
                'filters' => $filters,
                'minimum_should_match' => $minimumShouldMatch,
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
