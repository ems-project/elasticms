<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Elasticsearch\Document;

use EMS\CommonBundle\Elasticsearch\Response\ResponseInterface;

final class DocumentCollection implements DocumentCollectionInterface
{
    /** @var array<mixed> */
    private $documents;

    private function __construct()
    {
    }

    /**
     * @return DocumentCollection<DocumentInterface>
     */
    public static function fromResponse(ResponseInterface $response): DocumentCollection
    {
        $collection = new static();

        foreach ($response->getDocuments() as $document) {
            $collection->add($document);
        }

        return $collection;
    }

    public function count(): int
    {
        return \count((array) $this->documents);
    }

    /**
     * @return \Traversable<DocumentInterface>
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->documents);
    }

    private function add(DocumentInterface $document): void
    {
        $this->documents[] = $document;
    }
}
