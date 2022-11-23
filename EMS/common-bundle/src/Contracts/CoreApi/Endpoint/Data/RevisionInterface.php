<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Contracts\CoreApi\Endpoint\Data;

interface RevisionInterface
{
    public function getRevisionId(): int;

    public function getOuuid(): string;

    /**
     * @return array<string, mixed>
     */
    public function getRawData(): array;
}
