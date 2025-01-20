<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Contracts\Bridge\Core;

interface CoreDataBridgeInterface
{
    /** @param array<mixed> $rawData */
    public function create(array $rawData = []): int;

    public function discard(int $revisionId): bool;
}
