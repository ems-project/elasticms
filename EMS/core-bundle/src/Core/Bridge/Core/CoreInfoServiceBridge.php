<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Core\Bridge\Core;

use EMS\CommonBundle\Contracts\Bridge\Core\CoreInfoBridgeInterface;

class CoreInfoServiceBridge implements CoreInfoBridgeInterface
{
    #[\Override]
    public function documents(array $ouuids, array $environments = []): array
    {
        return $ouuids;
    }
}
