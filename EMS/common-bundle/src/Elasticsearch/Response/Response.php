<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Elasticsearch\Response;

use Elastica\Query;
use Elastica\Result;
use Elastica\ResultSet;
use EMS\CommonBundle\Elasticsearch\Aggregation\Aggregation;
use EMS\CommonBundle\Elasticsearch\Document\Document;
use EMS\CommonBundle\Elasticsearch\Document\DocumentCollection;
use EMS\CommonBundle\Elasticsearch\Document\DocumentCollectionInterface;
use EMS\CommonBundle\Elasticsearch\Document\DocumentInterface;

final class Response implements ResponseInterface
{
    /** @var int */
    private $total;

    /** @var array<int, mixed> */
    private readonly array $hits;

    /** @var string|null */
    private $scrollId;
    private bool $accurate = true;
    /** @var array<mixed> */
    private array $aggregations;

    /**
     * @param array<mixed> $response
     */
    private function __construct(array $response)
    {
        $this->total = $response['hits']['total']['value'] ?? $response['hits']['total'] ?? 0;

        $relation = $response['hits']['total']['relation'] ?? null;
        if (null !== $relation && 'eq' !== $relation) {
            $this->accurate = false;
        }
        $this->hits = $response['hits']['hits'] ?? [];
        $this->aggregations = $response['aggregations'] ?? [];
        $this->scrollId = $response['_scroll_id'] ?? null;
    }

    /**
     * @param array<string, mixed> $document
     */
    public static function fromArray(array $document): Response
    {
        return new self($document);
    }

    public static function fromResultSet(ResultSet $result): Response
    {
        return new self($result->getResponse()->getData());
    }

    #[\Override]
    public function hasDocuments(): bool
    {
        return \count($this->hits) > 0;
    }

    /**
     * @return DocumentInterface[]
     */
    #[\Override]
    public function getDocuments(): iterable
    {
        foreach ($this->hits as $hit) {
            yield Document::fromArray($hit);
        }
    }

    #[\Override]
    public function getDocument(int $index): DocumentInterface
    {
        return Document::fromArray($this->hits[$index]);
    }

    public function getAggregation(string $name): ?Aggregation
    {
        if (isset($this->aggregations[$name])) {
            return new Aggregation($name, $this->aggregations[$name]);
        }

        return null;
    }

    /**
     * @return iterable|Aggregation[]
     */
    public function getAggregations(): iterable
    {
        foreach ($this->aggregations as $name => $aggregation) {
            yield new Aggregation($name, $aggregation);
        }
    }

    #[\Override]
    public function getDocumentCollection(): DocumentCollectionInterface
    {
        return DocumentCollection::fromResponse($this);
    }

    #[\Override]
    public function getScrollId(): ?string
    {
        return $this->scrollId;
    }

    #[\Override]
    public function getTotal(): int
    {
        return $this->total;
    }

    public function getFormattedTotal(): string
    {
        $format = '%s';
        if (!$this->accurate) {
            $format = '≥%s';
        }

        return \sprintf($format, $this->total);
    }

    #[\Override]
    public function getTotalDocuments(): int
    {
        return \count($this->hits);
    }

    public function isAccurate(): bool
    {
        return $this->accurate;
    }

    #[\Override]
    public function buildResultSet(Query $query): ResultSet
    {
        $response = new \Elastica\Response([
            'timed_out' => false,
            'took' => 1,
            '_shards' => [
                'total' => 1,
                'successful' => 1,
                'skipped' => 0,
                'failed' => 0,
            ],
            'aggregations' => $this->aggregations,
            'hits' => [
                'hits' => $this->hits,
                'total' => [
                    'value' => $this->total,
                    'relation' => 'eq',
                ],
            ],
        ], 200);
        $response->getData();

        return new ResultSet($response, $query, \array_map(fn (array $p) => new Result($p), $this->hits));
    }
}
