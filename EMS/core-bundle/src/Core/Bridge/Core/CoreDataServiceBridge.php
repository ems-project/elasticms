<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Core\Bridge\Core;

use EMS\CommonBundle\Contracts\Bridge\Core\CoreDataBridgeInterface;
use EMS\CoreBundle\Service\Revision\RevisionService;

readonly class CoreDataServiceBridge implements CoreDataBridgeInterface
{
    public function __construct(
        private RevisionService $revisionService,
        private string $contentType,
    ) {
    }

    public function create(array $rawData = []): int
    {
        return $this->revisionService->create(contentType: $this->contentType, rawData: $rawData)->getId();
    }
}
