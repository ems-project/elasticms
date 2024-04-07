<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Service;

use EMS\CommonBundle\Entity\EntityInterface;

interface EntityServiceInterface
{
    public const I18N_PRIORITY = 1;
    public const FILTER_PRIORITY = 2;
    public const ANALYZER_PRIORITY = 3;
    public const ENVIRONMENT_PRIORITY = 4;
    public const MANAGED_ALIAS_PRIORITY = 5;
    public const FILE_PRIORITY = 6;
    public const WYSIWYG_PROFILE_PRIORITY = 7;
    public const WYSIWYG_STYLE_SET_PRIORITY = 8;
    public const FORM_PRIORITY = 9;
    public const ACTION_PRIORITY = 10;
    public const VIEW_PRIORITY = 11;
    public const QUERY_SEARCH_PRIORITY = 12;
    public const CONTENT_TYPE_PRIORITY = 13;
    public const CHANNEL_PRIORITY = 14;
    public const DASHBOARD_PRIORITY = 15;
    public const JOB_PRIORITY = 16;
    public const RELEASE_PRIORITY = 17;
    public const RELEASE_REVISION_PRIORITY = 18;
    public const SCHEDULE_PRIORITY = 19;
    public const DRAFT_PRIORITY = 20;
    public const USER_PRIORITY = 21;
    public const LOG_PRIORITY = 22;
    public const FORM_SUBMISSION_PRIORITY = 23;

    public function isSortable(): bool;

    /**
     * @param mixed $context
     *
     * @return EntityInterface[]
     */
    public function get(int $from, int $size, ?string $orderField, string $orderDirection, string $searchValue, $context = null): array;

    public function getEntityName(): string;

    /**
     * @return string[]
     */
    public function getAliasesName(): array;

    /**
     * @param mixed $context
     */
    public function count(string $searchValue = '', $context = null): int;

    public function getByItemName(string $name): ?EntityInterface;

    public function updateEntityFromJson(EntityInterface $entity, string $json): EntityInterface;

    public function createEntityFromJson(string $json, ?string $name = null): EntityInterface;

    public function deleteByItemName(string $name): string;

    public function getPriority(): int;
}
