<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\Bridge;

use EMS\CommonBundle\Contracts\Bridge\CoreBridgeInterface;
use EMS\CommonBundle\Contracts\CoreApi\CoreApiInterface;

readonly class CoreApiBridge implements CoreBridgeInterface
{
    public function __construct(private CoreApiInterface $coreApi)
    {
    }

    public function versions(): array
    {
        return $this->coreApi->admin()->getVersions();
    }
}
