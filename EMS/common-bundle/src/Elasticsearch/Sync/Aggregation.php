<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Elasticsearch\Sync;

use EMS\Helpers\Standard\Type;

class Aggregation
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
        if (!isset($this->response['buckets'])) {
            return false;
        }
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

    public function getValueAsString(): string
    {
        return Type::string($this->response['value_as_string']);
    }
}
