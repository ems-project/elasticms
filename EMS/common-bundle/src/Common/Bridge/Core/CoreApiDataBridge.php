<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\Bridge\Core;

use EMS\CommonBundle\Contracts\Bridge\Core\CoreDataBridgeInterface;
use EMS\CommonBundle\Contracts\CoreApi\CoreApiInterface;

readonly class CoreApiDataBridge implements CoreDataBridgeInterface
{
    public function __construct(private CoreApiInterface $coreApi)
    {
    }

    public function create(string $contentType, array $rawData = []): int
    {
        return $this->coreApi->data($contentType)->create($rawData)->getRevisionId();
    }
}
