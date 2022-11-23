<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Contracts\CoreApi\Endpoint\Data;

interface DraftInterface
{
    public function getRevisionId(): int;

    /**
     * Returns null if no uuid was passed to the create function.
     * Elasticms will create the ouuid.
     */
    public function getOuuid(): ?string;
}
