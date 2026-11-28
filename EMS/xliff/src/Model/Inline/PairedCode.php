<?php

declare(strict_types=1);

namespace EMS\Xliff\Model\Inline;

class PairedCode extends Node
{
    /** @var Node[] */
    private array $children = [];

    public function __construct(
        public readonly string $referenceId,
        public readonly string $id,
        public readonly string $endId,
        public readonly string $resourceName,
        public readonly string $equivalentOpeningText,
        public readonly string $equivalentClosingText,
    ) {
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
