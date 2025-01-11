<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\CoreApi\Endpoint\Data;

use EMS\CommonBundle\Common\CoreApi\Result;
use EMS\CommonBundle\Contracts\CoreApi\Endpoint\Data\RevisionInterface;

final readonly class Revision implements RevisionInterface
{
    private int $id;
    private string $ouuid;
    /** @var array<string, mixed> */
    private array $rawData;

    public function __construct(Result $result)
    {
        $data = $result->getData();

        $this->id = $data['id'];
        $this->ouuid = $data['ouuid'];
        $this->rawData = $data['revision'];
    }

    #[\Override]
    public function getRevisionId(): int
    {
        return $this->id;
    }

    #[\Override]
    public function getOuuid(): string
    {
        return $this->ouuid;
    }

    /**
     * @return array<string, mixed>
     */
    #[\Override]
    public function getRawData(): array
    {
        return $this->rawData;
    }
}
