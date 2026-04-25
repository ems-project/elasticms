<?php

declare(strict_types=1);

namespace EMS\Xliff\Model;

class UnitGroup implements DocumentNodeInterface
{
    /** @var DocumentNodeInterface[] */
    private array $nodes = [];
    /** @var Note[] */
    private array $notes = [];

    public function __construct(public readonly string $id, public readonly string $resourceName, public readonly ?string $type = null)
    {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getResourceName(): string
    {
        return $this->resourceName;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @return DocumentNodeInterface[]
     */
    public function getNodes(): array
    {
        return $this->nodes;
    }

    public function addNode(DocumentNodeInterface $node): void
    {
        $this->nodes[] = $node;
    }

    public function addNote(Note $note): void
    {
        $this->notes[] = $note;
    }

    /**
     * @return Note[]
     */
    public function getNotes(): array
    {
        return $this->notes;
    }

    /**
     * @return Segment[]
     */
    public function getSegments(): array
    {
        $segments = [];
        foreach ($this->getNodes() as $node) {
            $segments = [...$segments, ...$node->getSegments()];
        }

        return $segments;
    }
}
