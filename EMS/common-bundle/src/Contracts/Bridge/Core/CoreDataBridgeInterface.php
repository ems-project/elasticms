<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Contracts\Bridge\Core;

interface CoreDataBridgeInterface
{
    /** @param array<string, mixed> $data */
    public function draftAutoSave(int $revisionId, array $data): bool;

    /** @param array<string, mixed> $rawData */
    public function draftCreate(array $rawData = []): int;

    public function draftDiscard(int $revisionId): bool;

    public function finalize(int $revisionId): string;

    /** @return array{'id': int, 'data': array<string, mixed>} */
    public function getDraft(int $revisionId): array;
}
