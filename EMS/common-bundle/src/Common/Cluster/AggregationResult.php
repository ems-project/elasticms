<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\Cluster;

use EMS\Helpers\Standard\Type;

class AggregationResult
{

    /**
     * @param mixed[] $response
     */
    public function __construct(private readonly array $response)
    {
    }

    /**
     * @return iterable<BucketResponse>
     */
    public function getBuckets(): iterable
    {
        foreach (Type::array($this->response['buckets']) as $bucket) {
            yield new BucketResponse($bucket);
        }
    }
}