<?php

declare(strict_types=1);

namespace EMS\Xliff\Model;

final class Package
{
    /** @var Document[] */
    private array $documents = [];
    private string $sourceLocale;
    private string $targetLocale;

    public function __construct()
    {
    }

    public function setLocales(string $sourceLocale, string $targetLocale): void
    {
        $this->sourceLocale = $sourceLocale;
        $this->targetLocale = $targetLocale;
    }

    public function addDocument(string $id): Document
    {
        $document = new Document($id);
        $this->documents[] = $document;

        return $document;
    }

    /**
     * @return Document[]
     */
    public function getDocuments(): array
    {
        return $this->documents;
    }

    public function getSourceLocale(): string
    {
        return $this->sourceLocale;
    }

    public function getTargetLocale(): string
    {
        return $this->targetLocale;
    }
}
