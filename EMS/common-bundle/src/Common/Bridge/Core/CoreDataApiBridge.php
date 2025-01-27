<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\Bridge\Core;

use EMS\CommonBundle\Contracts\Bridge\Core\CoreDataBridgeInterface;
use EMS\CommonBundle\Contracts\CoreApi\Endpoint\Data\DataInterface;

readonly class CoreDataApiBridge implements CoreDataBridgeInterface
{
    public function __construct(private DataInterface $dataApi)
    {
    }

    #[\Override]
    public function autoSave(int $revisionId, array $rawData): bool
    {
        return $this->dataApi->autoSave($revisionId, $rawData);
    }

    #[\Override]
    public function create(array $rawData = []): int
    {
        return $this->dataApi->create($rawData)->getRevisionId();
    }

    #[\Override]
    public function delete(string $uuid): bool
    {
        return $this->dataApi->delete($uuid);
    }

    #[\Override]
    public function discard(int $revisionId): bool
    {
        return $this->dataApi->discard($revisionId);
    }

    #[\Override]
    public function getDraft(int $revisionId): array
    {
        return $this->dataApi->getDraft($revisionId);
    }

    #[\Override]
    public function finalize(int $revisionId, array $rawData = []): string
    {
        return $this->dataApi->finalize($revisionId, $rawData);
    }
}
