<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Contracts\Bridge\Core;

use EMS\CommonBundle\Common\Bridge\Core\CoreBridgeResponse;
use EMS\CommonBundle\Common\EMSLink;

interface CoreDataBridgeInterface
{
    /** @param array<string, mixed> $rawData */
    public function autoSave(int $revisionId, array $rawData): CoreBridgeResponse;

    /** @param array<string, mixed> $rawData */
    public function create(array $rawData = []): CoreBridgeResponse;

    public function delete(string $uuid): CoreBridgeResponse;

    public function discard(int $revisionId): CoreBridgeResponse;

    /** @param array<string, mixed> $rawData */
    public function finalize(int $revisionId, array $rawData = []): CoreBridgeResponse;

    public function getDraft(int $revisionId): CoreBridgeResponse;

    public function initDraft(string $uuid): CoreBridgeResponse;

    public function publish(EMSLink $emsLink, string $environment): CoreBridgeResponse;
}
