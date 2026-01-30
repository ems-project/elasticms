<?php

declare(strict_types=1);

namespace EMS\Xliff\Model;

use EMS\Helpers\Standard\Locale;
use EMS\Xliff\Id\SequentialIdGenerator;
use EMS\Xliff\Report\InsertReport;

final class Package
{
    /** @var Document[] */
    private array $documents = [];
    private string $sourceLocale;
    private string $targetLocale;
    private SequentialIdGenerator $idGenerator;

    public function __construct(private readonly ?InsertReport $insertReport = null)
    {
    }

    public function setLocales(string $sourceLocale, string $targetLocale): void
    {
        $this->sourceLocale = Locale::normalize($sourceLocale);
        $this->targetLocale = Locale::normalize($targetLocale);
        $this->idGenerator = new SequentialIdGenerator();
    }

    public function addDocument(string $id): Document
    {
        $document = new Document($this->idGenerator, $id, $this->insertReport);
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

    public function getInsertReport(): InsertReport
    {
        if (null === $this->insertReport) {
            throw new \RuntimeException('Unexpected null insert report');
        }

        return $this->insertReport;
    }
}
