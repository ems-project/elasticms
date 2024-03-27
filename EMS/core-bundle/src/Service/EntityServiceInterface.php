<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Service;

use EMS\CommonBundle\Entity\EntityInterface;

interface EntityServiceInterface
{
    const I18N_PRIORITY = 1;
    const FILTER_PRIORITY = 2;
    const ANALYZER_PRIORITY = 3;
    const ENVIRONMENT_PRIORITY = 4;
    const MANAGED_ALIAS_PRIORITY = 5;
    const FILE_PRIORITY = 6;
    const WYSIWYG_PROFILE_PRIORITY = 7;
    const WYSIWYG_STYLE_SET_PRIORITY = 8;
    const FORM_PRIORITY = 9;
    const ACTION_PRIORITY = 10;
    const VIEW_PRIORITY = 11;
    const QUERY_SEARCH_PRIORITY = 12;
    const CONTENT_TYPE_PRIORITY = 13;
    const CHANNEL_PRIORITY = 14;
    const DASHBOARD_PRIORITY = 15;
    const JOB_PRIORITY = 16;
    const RELEASE_PRIORITY = 17;
    const RELEASE_REVISION_PRIORITY = 18;
    const SCHEDULE_PRIORITY = 19;
    const DRAFT_PRIORITY = 20;
    const USER_PRIORITY = 21;
    const LOG_PRIORITY = 22;
    const FORM_SUBMISSION_PRIORITY = 23;

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
