<?php

declare(strict_types=1);

namespace EMS\Xliff\Model;

use EMS\Xliff\Model\Inline\Node;

class Segment
{
    /**
     * @param Node[] $sourceNodes
     * @param Node[] $targetNodes
     */
    public function __construct(public readonly array $sourceNodes, public readonly array $targetNodes, public readonly string $state)
    {
    }
}
