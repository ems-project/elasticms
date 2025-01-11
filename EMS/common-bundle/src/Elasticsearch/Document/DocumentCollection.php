<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Elasticsearch\Document;

use EMS\CommonBundle\Elasticsearch\Response\ResponseInterface;

final class DocumentCollection implements DocumentCollectionInterface
{
    private int $total = 0;

    /** @var DocumentInterface[] */
    private array $documents = [];

    private function __construct()
    {
    }

    /**
     * @return DocumentCollection<DocumentInterface>
     */
    public static function fromResponse(ResponseInterface $response): DocumentCollectionInterface
    {
        $collection = new self();
        $collection->total = $response->getTotal();

        foreach ($response->getDocuments() as $document) {
            $collection->add($document);
        }

        return $collection;
    }

    #[\Override]
    public function getTotal(): int
    {
        return $this->total;
    }

    #[\Override]
    public function count(): int
    {
        return \count($this->documents);
    }

    /**
     * @return \Traversable<DocumentInterface>
     */
    #[\Override]
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->documents);
    }

    private function add(DocumentInterface $document): void
    {
        $this->documents[] = $document;
    }
}
