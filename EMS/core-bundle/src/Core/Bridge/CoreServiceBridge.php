<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Core\Bridge;

use EMS\CommonBundle\Common\Composer\ComposerInfo;
use EMS\CommonBundle\Contracts\Bridge\CoreBridgeInterface;
use EMS\CoreBundle\Service\Revision\RevisionService;

readonly class CoreServiceBridge implements CoreBridgeInterface
{
    public function __construct(
        private RevisionService $revisionService,
        private ComposerInfo $composerInfo,
    ) {
    }

    public function versions(): array
    {
        return $this->composerInfo->getVersionPackages();
    }

    public function documentCreate(string $contentType, array $rawData = []): int
    {
        return $this->revisionService->create(contentType: $contentType, rawData: $rawData)->getId();
    }
}
