<?php

namespace EMS\CommonBundle\Elasticsearch\Aggregation;

class Aggregation
{
    private string $name;
    /** @var array<mixed> */
    private $buckets;
    /** @var int */
    private $count;
    /** @var array<mixed> */
    private $raw;

    /**
     * @param array<mixed> $aggregation
     */
    public function __construct(string $name, array $aggregation)
    {
        $this->name = $name;
        $this->buckets = $aggregation['buckets'] ?? [];
        $this->count = $aggregation['doc_count'] ?? 0;
        $this->raw = $aggregation;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return iterable<Bucket>|Bucket[]
     */
    public function getBuckets(): iterable
    {
        foreach ($this->buckets as $bucket) {
            yield new Bucket($bucket);
        }
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

    /**
     * @return string[]
     */
    public function getKeys(): array
    {
        $out = [];
        foreach ($this->buckets as $bucket) {
            if (!$bucket instanceof Bucket) {
                $bucket = new Bucket($bucket);
            }
            $key = $bucket->getKey();
            if (null === $key) {
                continue;
            }
            $out[] = $key;
        }

        return $out;
    }
}
