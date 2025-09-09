<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\Cluster;

use EMS\Helpers\Standard\Type;

class BucketResponse
{
    /**
     * @param mixed[] $response
     */
    public function __construct(private readonly array $response)
    {
    }

    public function getKey(): string
    {
        return Type::string($this->response['key']);
    }

    public function getDocCount(): int
    {
        return Type::integer($this->response['doc_count']);
    }

    public function getAggregation(string $aggregationName): AggregationResult
    {
        return new AggregationResult(Type::array($this->response[$aggregationName]));
    }
}
