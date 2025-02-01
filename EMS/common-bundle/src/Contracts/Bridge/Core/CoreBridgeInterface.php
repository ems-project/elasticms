<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Contracts\Bridge\Core;

interface CoreBridgeInterface
{
    /** @return array<mixed> */
    public function versions(): array;

    public function data(string $contentType): CoreDataBridgeInterface;

    public function info(): CoreInfoBridgeInterface;
}
