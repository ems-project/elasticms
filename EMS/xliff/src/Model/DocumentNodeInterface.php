<?php

declare(strict_types=1);

namespace EMS\Xliff\Model;

interface DocumentNodeInterface
{
    public function getId(): string;

    public function getResourceName(): ?string;

    public function getType(): ?string;

    public function addNote(Note $note): void;

    /**
     * @return Note[]
     */
    public function getNotes(): array;

    /**
     * @return Segment[]
     */
    public function getSegments(): array;
}
