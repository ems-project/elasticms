<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\Bridge\Core;

use EMS\CommonBundle\Contracts\Bridge\Core\CoreDataBridgeInterface;
use EMS\CommonBundle\Contracts\CoreApi\CoreApiInterface;

readonly class CoreApiDataBridge implements CoreDataBridgeInterface
{
    public function __construct(
        private CoreApiInterface $coreApi,
        private string $contentType
    ) {
    }

    public function create(array $rawData = []): int
    {
        return $this->coreApi->data($this->contentType)->create($rawData)->getRevisionId();
    }

    public function discard(int $revisionId): bool
    {
        return $this->coreApi->data($this->contentType)->discard($revisionId);
    }
}
