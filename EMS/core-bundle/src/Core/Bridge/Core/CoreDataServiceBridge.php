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

    #[\Override]
    public function draftAutoSave(int $revisionId, array $data): bool
    {
        $revision = $this->revisionService->getByRevisionId($revisionId);
        $this->revisionService->autoSave($revision, $data);

        return true;
    }

    #[\Override]
    public function draftCreate(array $rawData = []): int
    {
        return $this->revisionService->create(contentType: $this->contentType, rawData: $rawData)->getId();
    }

    #[\Override]
    public function draftDiscard(int $revisionId): bool
    {
        $revision = $this->revisionService->getByRevisionId($revisionId);
        $this->dataService->discardDraft($revision);

        return !$revision->hasId();
    }

    #[\Override]
    public function getDraft(int $revisionId): array
    {
        $revision = $this->revisionService->getByRevisionId($revisionId);

        return [
            'id' => $revision->getId(),
            'data' => $revision->getDraftData(),
        ];
    }
}
