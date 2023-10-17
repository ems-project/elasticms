<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Elasticsearch\Response;

use Elastica\Query;
use Elastica\ResultSet;
use EMS\CommonBundle\Elasticsearch\Document\DocumentCollection;
use EMS\CommonBundle\Elasticsearch\Document\DocumentInterface;

interface ResponseInterface
{
    public function hasDocuments(): bool;

    /**
     * @return DocumentInterface[]
     */
    public function getDocuments(): iterable;

    public function getDocument(int $index): DocumentInterface;

    /**
     * @return DocumentCollection<DocumentInterface>
     */
    public function getDocumentCollection(): DocumentCollection;

    public function getScrollId(): ?string;

    public function getTotal(): int;

    public function getTotalDocuments(): int;

    public function buildResultSet(Query $query, string $version): ResultSet;
}
