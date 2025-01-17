<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Contracts\Bridge;

interface CoreBridgeInterface
{
    /** @return array<mixed> */
    public function versions(): array;
}
