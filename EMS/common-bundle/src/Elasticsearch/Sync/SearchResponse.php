<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Elasticsearch\Sync;

use EMS\Helpers\Standard\Type;

class SearchResponse
{
    /**
     * @param mixed[] $response
     */
    public function __construct(private readonly array $response)
    {
    }

    public function getAggregation(string $aggregationName): Aggregation
    {
        return new Aggregation(Type::array($this->response['aggregations'][$aggregationName]));
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

    /**
     * @return list<string>
     */
    public function getIds(): array
    {
        return \array_values(\array_map(fn (array $result) => Type::string($result['_id']), $this->response['hits']['hits']));
    }

    /**
     * @return iterable<Hit>
     */
    public function getHits(): iterable
    {
        foreach ($this->response['hits']['hits'] as $hit) {
            yield new Hit($hit);
        }
    }

    public function getById(string $id): ?Hit
    {
        foreach ($this->response['hits']['hits'] as $hit) {
            $hit = new Hit($hit);
            if ($hit->getId() === $id) {
                return $hit;
            }
        }

        return null;
    }
}
