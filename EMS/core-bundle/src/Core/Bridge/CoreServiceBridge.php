<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Core\Bridge;

use EMS\CommonBundle\Common\Composer\ComposerInfo;
use EMS\CommonBundle\Contracts\Bridge\CoreBridgeInterface;

readonly class CoreServiceBridge implements CoreBridgeInterface
{
    public function __construct(private ComposerInfo $composerInfo)
    {
    }

    public function versions(): array
    {
        return $this->composerInfo->getVersionPackages();
    }
}
