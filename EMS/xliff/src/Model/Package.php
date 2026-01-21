<?php

declare(strict_types=1);

namespace EMS\Xliff\Model;

final class Package
{
    /** @var Document[] */
    public array $documents = [];

    public function __construct()
    {
    }

    public function addDocument(Document $document): void
    {
        $this->documents[] = $document;
    }

    /**
     * @return Document[]
     */
    public function getDocuments(): array
    {
        return $this->documents;
    }
}
