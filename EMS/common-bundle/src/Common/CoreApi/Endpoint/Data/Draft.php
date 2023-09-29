<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\CoreApi\Endpoint\Data;

use EMS\CommonBundle\Common\CoreApi\Result;
use EMS\CommonBundle\Contracts\CoreApi\Endpoint\Data\DraftInterface;

class Draft implements DraftInterface
{
    private readonly int $id;
    private readonly ?string $ouuid;

    public function __construct(Result $result)
    {
        $data = $result->getData();

        $this->id = $data['revision_id'];
        $this->ouuid = $data['ouuid'] ?? null;
    }

    public function getRevisionId(): int
    {
        return $this->id;
    }

    public function getOuuid(): ?string
    {
        return $this->ouuid;
    }
}
