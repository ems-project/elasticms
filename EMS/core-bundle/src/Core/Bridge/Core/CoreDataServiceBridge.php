<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Core\Bridge\Core;

use EMS\CommonBundle\Contracts\Bridge\Core\CoreDataBridgeInterface;
use EMS\CoreBundle\Service\Revision\RevisionService;

readonly class CoreDataServiceBridge implements CoreDataBridgeInterface
{
    public function __construct(
        private RevisionService $revisionService
    ) {
    }

    public function create(string $contentType, array $rawData = []): int
    {
        return $this->revisionService->create(contentType: $contentType, rawData: $rawData)->getId();
    }
}
