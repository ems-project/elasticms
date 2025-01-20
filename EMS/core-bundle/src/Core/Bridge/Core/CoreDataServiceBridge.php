<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Core\Bridge\Core;

use EMS\CommonBundle\Contracts\Bridge\Core\CoreDataBridgeInterface;
use EMS\CoreBundle\Service\DataService;
use EMS\CoreBundle\Service\Revision\RevisionService;

readonly class CoreDataServiceBridge implements CoreDataBridgeInterface
{
    public function __construct(
        private DataService $dataService,
        private RevisionService $revisionService,
        private string $contentType,
    ) {
    }

    public function create(array $rawData = []): int
    {
        return $this->revisionService->create(contentType: $this->contentType, rawData: $rawData)->getId();
    }

    public function discard(int $revisionId): bool
    {
        $revision = $this->revisionService->getByRevisionId($revisionId);
        $this->dataService->discardDraft($revision);

        return !$revision->hasId();
    }
}
