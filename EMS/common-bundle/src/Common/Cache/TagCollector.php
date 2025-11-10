<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\Cache;

use Elastica\ResultSet;
use EMS\CommonBundle\Elasticsearch\Document\EMSSource;
use EMS\CommonBundle\Search\Search;

class TagCollector
{
    /** @var array<string, bool> */
    private array $uuids = [];
    /** @var array<string, bool> */
    private array $indices = [];
    /** @var array<string, bool> */
    private array $contentTypes = [];

    public function add(Search $search, ResultSet $resultSet): void
    {
        foreach ($search->getIndices() as $index) {
            $this->indices[$index] = true;
        }
        foreach ($resultSet->getResults() as $result) {
            $this->uuids[$result->getId()] = true;
            $contentType = $result->getSource()[EMSSource::FIELD_CONTENT_TYPE] ?? null;
            if (null !== $contentType) {
                $this->contentTypes[$contentType] = true;
            }
        }
    }

    /**
     * @return string[]
     */
    public function all(): array
    {
        return \array_keys(\array_merge($this->uuids, $this->indices, $this->contentTypes));
    }

    public function isEmpty(): bool
    {
        return empty($this->uuids) && empty($this->indices) && empty($this->contentTypes);
    }
}
