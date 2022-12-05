<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\CoreApi\Endpoint\Data;

use EMS\CommonBundle\Common\CoreApi\Result;
use EMS\CommonBundle\Contracts\CoreApi\Endpoint\Data\RevisionInterface;

final class Revision implements RevisionInterface
{
    private readonly int $id;
    private readonly string $ouuid;
    /** @var array<string, mixed> */
    private readonly array $rawData;

    public function __construct(Result $result)
    {
        $data = $result->getData();

        $this->id = $data['id'];
        $this->ouuid = $data['ouuid'];
        $this->rawData = $data['revision'];
    }

    public function getRevisionId(): int
    {
        return $this->id;
    }

    public function getOuuid(): string
    {
        return $this->ouuid;
    }

    /**
     * @return array<string, mixed>
     */
    public function getRawData(): array
    {
        return $this->rawData;
    }
}
