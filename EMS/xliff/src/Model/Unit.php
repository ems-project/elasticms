<?php

declare(strict_types=1);

namespace EMS\Xliff\Model;

class Unit implements DocumentNodeInterface
{
    /** @var Segment[] */
    private array $segments = [];
    /** @var Note[] */
    private array $notes = [];

    public function __construct(public readonly string $id, public readonly string $resourceName, public readonly ?string $type = null)
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
}
