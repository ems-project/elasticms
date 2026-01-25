<?php

declare(strict_types=1);

namespace EMS\Xliff\Model\Inline;

abstract class Node
{
    /** @var Node[] */
    private array $children = [];

    public function __construct()
    {
    }

    /**
     * @param Node[] $node
     */
    public function addChild(array $node): void
    {
        $this->children = \array_merge($this->children, $node);
    }

    /**
     * @return Node[]
     */
    public function getChildren(): array
    {
        return $this->children;
    }
}
