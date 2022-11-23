<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Contracts\CoreApi\Endpoint\Data;

use EMS\CommonBundle\Contracts\CoreApi\CoreApiExceptionInterface;

interface DataInterface
{
    public const MODE_UPDATE = 1;
    public const MODE_REPLACE = 2;

    /**
     * @param array<string, mixed> $rawData
     *
     * @throws CoreApiExceptionInterface
     */
    public function create(array $rawData, ?string $ouuid = null): DraftInterface;

    /**
     * @throws CoreApiExceptionInterface
     */
    public function delete(string $ouuid): bool;

    /**
     * @throws CoreApiExceptionInterface
     */
    public function discard(int $revisionId): bool;

    /**
     * @throws CoreApiExceptionInterface
     */
    public function finalize(int $revisionId): string;

    /**
     * @throws CoreApiExceptionInterface
     */
    public function get(string $ouuid): RevisionInterface;

    /**
     * @throws CoreApiExceptionInterface
     */
    public function head(string $ouuid): bool;

    /**
     * @param array<string, mixed> $rawData
     *
     * @throws CoreApiExceptionInterface
     */
    public function replace(string $ouuid, array $rawData): DraftInterface;

    /**
     * @param array<string, mixed> $rawData
     *
     * @throws CoreApiExceptionInterface
     */
    public function update(string $ouuid, array $rawData): DraftInterface;

    /**
     * @param array<string, mixed> $rawData
     *
     * @throws CoreApiExceptionInterface
     */
    public function save(string $ouuid, array $rawData, int $mode = self::MODE_UPDATE, bool $discardDraft = true): int;
}
