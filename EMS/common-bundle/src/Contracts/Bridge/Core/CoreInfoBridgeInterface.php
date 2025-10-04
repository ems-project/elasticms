<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Contracts\Bridge\Core;

use EMS\CommonBundle\Common\Bridge\Core\CoreBridgeResponse;
use EMS\CommonBundle\Common\EMSLink;

interface CoreInfoBridgeInterface
{
    /**
     * @param list<string> $environments
     */
    public function documents(array $environments, EMSLink ...$emsLinks): CoreBridgeResponse;
}
