<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Core\Bridge\Core;

use EMS\CommonBundle\Common\Composer\ComposerInfo;
use EMS\CommonBundle\Contracts\Bridge\Core\CoreBridgeInterface;
use EMS\CommonBundle\Contracts\Bridge\Core\CoreDataBridgeInterface;
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

    public function data(): CoreDataBridgeInterface
    {
        return new CoreDataServiceBridge($this->revisionService);
    }
}
