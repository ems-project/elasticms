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
}
