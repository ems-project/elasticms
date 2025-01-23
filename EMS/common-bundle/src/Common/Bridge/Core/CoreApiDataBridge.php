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

    #[\Override]
    public function autoSave(int $revisionId, array $rawData): bool
    {
        return $this->coreApi->data($this->contentType)->autoSave($revisionId, $rawData);
    }

    #[\Override]
    public function create(array $rawData = []): int
    {
        return $this->coreApi->data($this->contentType)->create($rawData)->getRevisionId();
    }

    #[\Override]
    public function delete(string $ouuid): bool
    {
        return $this->coreApi->data($this->contentType)->delete($ouuid);
    }

    #[\Override]
    public function discard(int $revisionId): bool
    {
        return $this->coreApi->data($this->contentType)->discard($revisionId);
    }

    #[\Override]
    public function getDraft(int $revisionId): array
    {
        return $this->coreApi->data($this->contentType)->getDraft($revisionId);
    }

    #[\Override]
    public function finalize(int $revisionId, array $rawData = []): string
    {
        return $this->coreApi->data($this->contentType)->finalize($revisionId, $rawData);
    }
}
