<?php

declare(strict_types=1);

namespace EMS\Xliff\Model\Inline;

class Group extends Node
{
    /** @var Node[] */
    private array $children = [];

    public function __construct(
        public readonly string $id,
        public readonly string $type,
    ) {
        parent::__construct();
    }

    /**
     * @param Node[] $node
     */
    public function addChildren(array $node): void
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
