<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Contracts\Bridge\Core;

interface CoreDataBridgeInterface
{
    /** @param array<string, mixed> $rawData */
    public function autoSave(int $revisionId, array $rawData): bool;

    /** @param array<string, mixed> $rawData */
    public function create(array $rawData = []): int;

    public function delete(string $uuid): bool;

    public function discard(int $revisionId): bool;

    /** @param array<string, mixed> $rawData */
    public function finalize(int $revisionId, array $rawData = []): string;

    /** @return array{'id': int, 'data': array<string, mixed>} */
    public function getDraft(int $revisionId): array;
}
