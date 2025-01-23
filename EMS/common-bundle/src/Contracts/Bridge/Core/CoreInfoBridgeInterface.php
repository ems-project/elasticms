<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Contracts\Bridge\Core;

interface CoreInfoBridgeInterface
{
    /**
     * @param list<string> $uuids
     * @param list<string> $environments
     *
     * @return string[]
     */
    public function documents(array $uuids, array $environments = []): array;
}
