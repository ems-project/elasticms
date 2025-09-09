<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\Cluster;

use EMS\Helpers\Standard\Type;

class SearchResult
{
    /**
     * @param mixed[] $response
     */
    public function __construct(private readonly array $response)
    {
    }

    public function getAggregation(string $aggregationName): AggregationResult
    {
        return new AggregationResult(Type::array($this->response['aggregations'][$aggregationName]));
    }

    public function getScrollId(): string
    {
        return Type::string($this->response['_scroll_id']);
    }

    public function countHits(): int
    {
        return \count(Type::array($this->response['hits']['hits'] ?? []));
    }

    public function getTotal(): int
    {
        return Type::integer($this->response['hits']['total']['value']);
    }
}
