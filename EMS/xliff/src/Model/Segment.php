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
    public function __construct(private array $sourceNodes, private array $targetNodes, private string $state)
    {
    }

    /**
     * @return Node[]
     */
    public function getSourceNodes(): array
    {
        return $this->sourceNodes;
    }

    /**
     * @return Node[]
     */
    public function getTargetNodes(): array
    {
        return $this->targetNodes;
    }

    /**
     * @param Node[] $nodes
     */
    public function addSourceNodes(array $nodes): void
    {
        $this->sourceNodes = \array_merge($this->sourceNodes, $nodes);
    }

    /**
     * @param Node[] $nodes
     */
    public function addTargetNodes(array $nodes): void
    {
        $this->targetNodes = \array_merge($this->targetNodes, $nodes);
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function setState(string $state): void
    {
        $this->state = $state;
    }
}
