<?php

namespace EMS\CommonBundle\Elasticsearch\Document;

/**
 * @extends \IteratorAggregate<int, DocumentInterface>
 */
interface DocumentCollectionInterface extends \IteratorAggregate, \Countable
{
    public function count(): int;

    /**
     * @return DocumentInterface[]
     */
    public function getIterator(): iterable;
}
