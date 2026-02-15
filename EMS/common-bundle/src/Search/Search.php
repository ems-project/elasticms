<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Search;

use Elastica\Aggregation\AbstractAggregation;
use Elastica\Aggregation\Terms;
use Elastica\Query\AbstractQuery;
use Elastica\Search as ElasticaSearch;
use Elastica\Suggest;
use EMS\CommonBundle\Elasticsearch\Aggregation\ElasticaAggregation;
use EMS\CommonBundle\Elasticsearch\Document\EMSSource;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class Search
{
    /** @var string[] */
    private array $sourceIncludes = [];
    /** @var string[] */
    private array $sourceExcludes = [];
    /** @var string[] */
    private array $contentTypes = [];
    /** @var AbstractAggregation[] */
    private array $aggregations = [];
    private int $size = 10;
    private int $from = 0;
    /** @var array<mixed>|null */
    private ?array $sort = null;
    private ?AbstractQuery $postFilter = null;
    private ?Suggest $suggest = null;
    /** @var array<mixed>|null */
    private ?array $highlight = null;

    private ?string $regex = null;

    /**
     * @param string[]                        $indices
     * @param AbstractQuery|array<mixed>|null $query
     */
    public function __construct(private readonly array $indices, private $query = null)
    {
    }

    public function serialize(string $format = 'json'): string
    {
        return self::getSerializer()->serialize($this, $format, [AbstractNormalizer::IGNORED_ATTRIBUTES => ['query', 'aggregations']]);
    }

    public static function deserialize(string $data, string $format = 'json'): Search
    {
        $data = self::getSerializer()->deserialize($data, Search::class, $format);
        if (!$data instanceof Search) {
            throw new \RuntimeException('Unexpected search object');
        }

        return $data;
    }

    public function hasSources(): bool
    {
        return [] !== $this->sourceIncludes || [] !== $this->sourceExcludes;
    }

    /**
     * @return array<mixed>
     */
    public function getSources(): array
    {
        if ([] !== $this->sourceExcludes) {
            return \array_filter([
                'includes' => \array_values(\array_unique(\array_merge($this->sourceIncludes, EMSSource::REQUIRED_FIELDS))),
                'excludes' => $this->sourceExcludes,
            ]);
        }

        return \array_values(\array_unique(\array_merge($this->sourceIncludes, EMSSource::REQUIRED_FIELDS)));
    }

    /**
     * @return list<string>
     */
    public function getContentTypes(): array
    {
        return \array_values($this->contentTypes);
    }

    /**
     * @return AbstractQuery|array<mixed>|null
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @return array<mixed>|null
     */
    public function getQueryArray(): ?array
    {
        if ($this->query instanceof AbstractQuery) {
            return $this->query->toArray();
        }

        return $this->query;
    }

    /**
     * @param array<mixed>|null $query
     */
    public function setQueryArray(?array $query): void
    {
        $this->query = $query;
    }

    /**
     * @param array<mixed> $sources
     */
    public function setSources(array $sources): void
    {
        if ([] === $sources) {
            $this->sourceIncludes = [];

            return;
        }

        if (isset($sources['includes']) || isset($sources['excludes'])) {
            $this->sourceIncludes = $sources['includes'] ?? [];
            $this->sourceExcludes = $sources['excludes'] ?? [];
        } else {
            $this->sourceIncludes = $sources;
        }
    }

    /**
     * @param string[] $sourceExcludes
     */
    public function setSourceExcludes(array $sourceExcludes): void
    {
        $this->sourceExcludes = $sourceExcludes;
    }

    /**
     * @param string[] $contentTypes
     */
    public function setContentTypes(array $contentTypes): void
    {
        $this->contentTypes = $contentTypes;
    }

    /**
     * @return string[]
     */
    public function getIndices(): array
    {
        return $this->indices;
    }

    public function setSize(int $size): void
    {
        $this->size = $size;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function getFrom(): int
    {
        return $this->from;
    }

    public function setFrom(int $from): void
    {
        $this->from = $from;
    }

    /**
     * @return array{size: int, from: int}
     */
    public function getSearchOptions(): array
    {
        return [
            ElasticaSearch::OPTION_SIZE => $this->size,
            ElasticaSearch::OPTION_FROM => $this->from,
        ];
    }

    /**
     * @return array<mixed>|null
     */
    public function getSort(): ?array
    {
        return $this->sort;
    }

    /**
     * @param array<mixed>|null $sort
     */
    public function setSort(?array $sort): void
    {
        $this->sort = $sort;
    }

    /**
     * @return array<mixed>
     */
    public function getScrollOptions(): array
    {
        return [
            ElasticaSearch::OPTION_SIZE => $this->size,
        ];
    }

    /**
     * @return array<mixed>
     */
    public function getCountOptions(): array
    {
        return [];
    }

    /**
     * @param AbstractAggregation[] $aggregations
     */
    public function addAggregations(array $aggregations): void
    {
        $this->aggregations = \array_merge($this->aggregations, $aggregations);
    }

    public function addAggregation(AbstractAggregation $aggregation): void
    {
        $this->aggregations[] = $aggregation;
    }

    /**
     * @return AbstractAggregation[]
     */
    public function getAggregations(): array
    {
        return $this->aggregations;
    }

    /**
     * @param array<mixed> $aggs
     */
    public function setAggs(array $aggs): void
    {
        $this->aggregations = self::parseAggs($aggs);
    }

    /**
     * @return mixed[]
     */
    public function getAggs(): array
    {
        $aggs = [];
        foreach ($this->aggregations as $aggregation) {
            $aggs[$aggregation->getName()] = $aggregation->toArray();
        }

        return $aggs;
    }

    public function addTermsAggregation(string $name, string $field, int $size = 20): void
    {
        $termsAggregation = new Terms($name);
        $termsAggregation->setField($field);
        $termsAggregation->setSize($size);
        $this->addAggregation($termsAggregation);
    }

    public function setPostFilter(?AbstractQuery $postFilter): void
    {
        $this->postFilter = $postFilter;
    }

    public function getPostFilter(): ?AbstractQuery
    {
        return $this->postFilter;
    }

    public function getSuggest(): ?Suggest
    {
        return $this->suggest;
    }

    public function setSuggest(?Suggest $suggest): void
    {
        $this->suggest = $suggest;
    }

    /**
     * @return array<mixed>|null
     */
    public function getHighlight(): ?array
    {
        return $this->highlight;
    }

    /**
     * @param array<mixed> $highlight
     */
    public function setHighlight(?array $highlight): void
    {
        $this->highlight = $highlight;
    }

    public function getRegex(): ?string
    {
        return $this->regex;
    }

    public function setRegex(?string $regex): void
    {
        $this->regex = $regex;
    }

    public static function getSerializer(): Serializer
    {
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];

        return new Serializer($normalizers, $encoders);
    }

    /**
     * @param array<mixed> $aggs
     *
     * @return ElasticaAggregation[]
     */
    public static function parseAggs(array $aggs): array
    {
        $aggregations = [];
        foreach ($aggs as $name => $agg) {
            $aggregations[] = self::parseAgg($name, $agg);
        }

        return $aggregations;
    }

    /**
     * @param array<mixed> $agg
     */
    private static function parseAgg(string $name, array $agg): ElasticaAggregation
    {
        $subAggregations = [];
        if (isset($agg['aggs'])) {
            $subAggregations = self::parseAggs($agg['aggs']);
            unset($agg['aggs']);
        }
        if (1 !== \count($agg)) {
            throw new \RuntimeException('Unexpected aggregation basename');
        }
        $aggregation = new ElasticaAggregation($name);
        foreach ($agg as $basename => $rule) {
            $aggregation->setConfig($basename, $rule);
            foreach ($subAggregations as $subAggregation) {
                $aggregation->addAggregation($subAggregation);
            }
        }

        return $aggregation;
    }
}
