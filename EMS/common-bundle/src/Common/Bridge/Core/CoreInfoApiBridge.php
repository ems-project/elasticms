<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\Bridge\Core;

use EMS\CommonBundle\Contracts\Bridge\Core\CoreInfoBridgeInterface;

class CoreInfoApiBridge implements CoreInfoBridgeInterface
{
    #[\Override]
    public function documents(array $ouuids, array $environments = []): array
    {
        return $ouuids;
    }
}
