<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Twig;

use EMS\CommonBundle\Contracts\Bridge\Core\CoreBridgeInterface;

readonly class CoreBridgeRuntime
{
    public function __construct(private CoreBridgeInterface $coreBridge)
    {
    }

    public function build(): CoreBridgeInterface
    {
        return $this->coreBridge;
    }
}
