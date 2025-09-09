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

    public function hasKey(string $key): bool
    {
        foreach (Type::array($this->response['buckets']) as $bucket) {
            $bucket = new BucketResponse($bucket);
            if ($bucket->getKey() === $key) {
                return true;
            }
        }

        return false;
    }

    public function getBucketByKey(string $key): BucketResponse
    {
        foreach (Type::array($this->response['buckets']) as $bucket) {
            $bucket = new BucketResponse($bucket);
            if ($bucket->getKey() === $key) {
                return $bucket;
            }
        }
        throw new \RuntimeException(\sprintf('No bucket found for key %s', $key));
    }
}
