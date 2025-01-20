<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Contracts\Bridge\Core;

interface CoreDataBridgeInterface
{
    /** @param array<mixed> $rawData */
    public function draftCreate(array $rawData = []): int;

    public function draftDiscard(int $revisionId): bool;
}
