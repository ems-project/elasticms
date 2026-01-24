<?php

declare(strict_types=1);

namespace EMS\Xliff\Model;

class Unit
{
    /** @var Segment[] */
    private array $segments = [];

    public function __construct(public readonly string $id, public readonly string $resourceName, public readonly string $type)
    {
    }

    public function addSegment(Segment $segment): void
    {
        $this->segments[] = $segment;
    }

    /**
     * @return Segment[]
     */
    public function getSegments(): array
    {
        return $this->segments;
    }
}
