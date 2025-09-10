<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Elasticsearch\Sync;

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

    public function getAggregation(string $aggregationName): Aggregation
    {
        return new Aggregation(Type::array($this->response[$aggregationName]));
    }
}
