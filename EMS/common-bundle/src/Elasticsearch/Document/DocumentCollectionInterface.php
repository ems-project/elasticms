<?php

namespace EMS\CommonBundle\Elasticsearch\Document;

/**
 * @extends \IteratorAggregate<int, DocumentInterface>
 */
interface DocumentCollectionInterface extends \IteratorAggregate, \Countable
{
    public function count(): int;

    /**
     * @return \Traversable<DocumentInterface>
     */
    public function getIterator(): \Traversable;
}
