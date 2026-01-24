<?php

declare(strict_types=1);

namespace EMS\Xliff\Model;

use EMS\Xliff\Model\Inline\InlineInterface;

class Segment
{
    /**
     * @param InlineInterface[] $sourceNodes
     * @param InlineInterface[] $targetNodes
     */
    public function __construct(public readonly array $sourceNodes, public readonly array $targetNodes, public readonly string $state)
    {
    }
}
