<?php

namespace EMS\CommonBundle\Elasticsearch\Aggregation;

class Bucket
{
    /** @var string|null */
    private $key;
    private readonly int $count;
    /** @var array<string, mixed> */
    private readonly array $raw;

    /**
     * @param array<string, mixed> $bucket
     */
    public function __construct(array $bucket)
    {
        $this->key = $bucket['key'] ?? null;
        $this->count = $bucket['doc_count'] ?? 0;
        $this->raw = $bucket;
    }

    /**
     * @return \Traversable<Bucket>
     */
    public function getSubBucket(string $name): \Traversable
    {
        foreach ($this->raw[$name]['buckets'] ?? [] as $bucket) {
            yield new Bucket($bucket);
        }
    }

    public function getKey(): ?string
    {
        return $this->key;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    /**
     * @return array<mixed>
     */
    public function getRaw(): array
    {
        return $this->raw;
    }
}
