<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Search;

use Elastica\Aggregation\AbstractAggregation;
use Elastica\Aggregation\Filter as FilterAggregation;
use Elastica\Aggregation\Nested as NestedAggregation;
use Elastica\Aggregation\ReverseNested;
use Elastica\Aggregation\Terms as TermsAggregation;
use Elastica\Query\AbstractQuery;
use Elastica\Query\BoolQuery;
use Elastica\Query\Nested;
use Elastica\Query\Simple;
use Elastica\Query\Terms;
use Elastica\Suggest;
use Elastica\Suggest\Term;
use EMS\ClientHelperBundle\Helper\Elasticsearch\ClientRequest;
use EMS\CommonBundle\Search\Search as CommonSearch;

final class QueryBuilder
{
    public function __construct(
        private readonly ClientRequest $clientRequest,
        private readonly Search $search
    ) {
    }

    /**
     * @param string[] $types
     */
    public function buildSearch(array $types): CommonSearch
    {
        $query = $this->getQuery();
        $search = $this->clientRequest->initializeCommonSearch($types, $query);
        $search->setPostFilter($this->getPostFilters());
        $hasPostFilter = (null !== $search->getPostFilter());
        foreach ($this->getAggs($hasPostFilter) as $aggregation) {
            $search->addAggregation($aggregation);
        }
        $search->setSort($this->getSort());
        $suggest = $this->getSuggest();
        if (null !== $suggest) {
            $search->setSuggest($suggest);
        }
        $search->setHighlight($this->search->getHighlight());

        return $search;
    }

    private function getQuery(): ?AbstractQuery
    {
        $queryString = $this->search->getQueryString();

        return $queryString ? $this->getQueryWithString($queryString) : $this->getQueryFilters();
    }

    private function getQueryWithString(string $queryString): ?AbstractQuery
    {
        if (null === $querySearch = $this->search->getQuerySearch($queryString)) {
            return $this->getQueryWithStringAnalyzed($queryString);
        }

        return new Simple($querySearch);
    }

    private function getQueryWithStringAnalyzed(string $queryString): ?AbstractQuery
    {
        $query = new BoolQuery();
        if ($this->getQueryFilters()) {
            $query->addMust($this->getQueryFilters());
        }

        $analyzer = new Analyzer($this->clientRequest);
        $tokens = $this->clientRequest->analyze($queryString, $this->search->getAnalyzer());

        $queryFields = new BoolQuery();
        foreach ($this->search->getFields() as $field) {
            $textValues = $analyzer->getTextValues($field, $this->search->getAnalyzer(), $tokens, $this->search->getSynonyms());
            if (0 === \count($textValues)) {
                continue;
            }

            $textMust = new BoolQuery();
            $textMust->addMust(\array_values(\array_map(fn (TextValue $textValue) => $textValue->makeShould(), $textValues)));

            $queryFields->setMinimumShouldMatch(1)->addShould($textMust);
        }
        if ($queryFields->count() > 0) {
            $query->addMust($queryFields);
        }

        return $query->count() > 0 ? $query : null;
    }

    public function getQueryFilters(): ?BoolQuery
    {
        $query = new BoolQuery();

        foreach ($this->search->getQueryFacets() as $field => $terms) {
            $query->addMust(new Terms($field, $terms));
        }

        foreach ($this->search->getFilters() as $filter) {
            if (!$filter->isActive() || $filter->isPostFilter()) {
                continue;
            }
            $queryFilter = $filter->getQuery();
            if (null === $queryFilter) {
                continue;
            }

            $nestedPath = $filter->getNestedPath();
            if (null !== $nestedPath) {
                $nested = new Nested();
                $nested->setPath($nestedPath);
                $nested->setQuery($queryFilter);
                $nested->setParam('ignore_unmapped', true);
                $query->addMust($nested);
            } else {
                $query->addMust($queryFilter);
            }
        }

        if (0 === $query->count()) {
            return null;
        }

        return $query;
    }

    private function getPostFilters(Filter $exclude = null): ?AbstractQuery
    {
        $postFilters = new BoolQuery();

        foreach ($this->search->getFilters() as $filter) {
            if (!$filter->isActive() || !$filter->isPostFilter() || $filter === $exclude) {
                continue;
            }
            $query = $filter->getQuery();
            if (null === $query) {
                continue;
            }

            $filterNestedPath = $filter->getNestedPath();
            if (null !== $filterNestedPath) {
                $nested = new Nested();
                $nested->setPath($filterNestedPath);
                $nested->setQuery($query);
                $nested->setParam('ignore_unmapped', true);

                $postFilters->addMust($nested);
            } else {
                $postFilters->addMust($query);
            }
        }

        if (0 === \count($postFilters)) {
            return null;
        }

        return $postFilters;
    }

    /**
     * @return AbstractAggregation[]
     */
    private function getAggs(bool $hasPostFilter = false): array
    {
        $aggs = [];

        foreach ($this->search->getQueryFacets() as $facet => $size) {
            $terms = new TermsAggregation($facet);
            $terms->setField($facet);
            $terms->setSize($size);
            $aggs[$facet] = $terms;
        }

        foreach ($this->search->getFilters() as $filter) {
            if (!$filter->hasAggSize()) {
                continue;
            }

            $aggregation = $hasPostFilter ? $this->getAggPostFilter($filter) : $this->getAgg($filter);
            $aggs[$filter->getName()] = $aggregation;
        }

        return \array_filter($aggs);
    }

    private function getAgg(Filter $filter): AbstractAggregation
    {
        $agg = new TermsAggregation($filter->getName());
        $agg->setField($filter->getField());
        $aggSize = $filter->getAggSize();
        if (null !== $aggSize) {
            $agg->setSize($aggSize);
        }

        if ($filter->isReversedNested()) {
            $subAggregation = new ReverseNested('reversed_nested');
            $agg->addAggregation($subAggregation);
        }

        $orderField = $filter->getSortField();
        if (null !== $orderField) {
            $agg->setOrder($orderField, $filter->getSortOrder());
        }

        if ($filter->isNested() && null !== $path = $filter->getNestedPath()) {
            $nestedAggregation = new NestedAggregation($filter->getName(), $path);
            $agg = $nestedAggregation->addAggregation($agg);
        }

        return $agg;
    }

    /**
     * If the search uses post filtering, we need to filter other post filter aggregation.
     */
    private function getAggPostFilter(Filter $filter): AbstractAggregation
    {
        $agg = $this->getAgg($filter);
        $postFilters = $this->getPostFilters($filter);

        if (null === $postFilters) {
            return $agg;
        }
        $filterAggregation = new FilterAggregation($filter->getName());
        $filterAggregation->setFilter($postFilters);

        $agg->setName('filtered_'.$filter->getName());
        $filterAggregation->addAggregation($agg);

        return $filterAggregation;
    }

    private function getSuggest(): ?Suggest
    {
        $queryString = $this->search->getQueryString();
        if (null === $queryString) {
            return null;
        }

        $suggest = new Suggest();
        foreach ($this->search->getSuggestFields() as $field) {
            $term = new Term('suggest-'.$field, $field);
            $term->setText($queryString);
            $suggest->addSuggestion($term);
        }

        return $suggest;
    }

    /**
     * @return array<mixed>
     */
    private function getSort(): array
    {
        if (null === $sort = $this->search->getSort()) {
            return $this->buildSort($this->search->getDefaultSorts());
        }

        return $this->buildSort([$sort]);
    }

    /**
     * @param array<mixed> $searchSorts
     *
     * @return array<string, mixed>
     */
    private function buildSort(array $searchSorts): array
    {
        $sorts = [];

        foreach ($searchSorts as $sort) {
            $field = $sort['field'] ?? null;
            if (!\is_string($field)) {
                throw new \RuntimeException('Unexpected not named search sort');
            }

            $includeScore = $sort['score'] ?? false;

            unset($sort['field'], $sort['score']);
            $sorts[$field] = $sort;

            if ($includeScore) {
                $sorts['_score'] = ['order' => 'desc'];
            }
        }

        return $sorts;
    }
}
