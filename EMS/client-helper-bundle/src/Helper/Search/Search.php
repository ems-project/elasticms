<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Search;

use Elastica\Query\AbstractQuery;
use Elastica\Query\BoolQuery;
use EMS\ClientHelperBundle\Helper\Elasticsearch\ClientRequest;
use EMS\ClientHelperBundle\Helper\Request\RequestHelper;
use EMS\CommonBundle\Elasticsearch\QueryStringEscaper;
use EMS\CommonBundle\Elasticsearch\Response\Response;
use EMS\Helpers\Standard\Json;
use Symfony\Component\HttpFoundation\Request;

use function Symfony\Component\String\u;

final class Search
{
    private ?string $indexRegex;
    /** @var string[] */
    private readonly array $types;
    /** @var array<string, int> [facet_name => size], used for aggregation */
    private readonly array $facets;
    /** @var Synonym[] */
    private array $synonyms = [];
    /** @var string[] */
    private array $fields = [];
    /** @var string[] */
    private readonly array $fieldsExclude;
    /** @var ?array<mixed> */
    private ?array $querySearch = null;
    /** @var string[] */
    private array $suggestFields = [];
    /** @var Filter[] */
    private array $filters = [];
    /** @var int[] */
    private array $sizes;
    /** @var array<mixed> */
    private readonly array $defaultSorts;
    /** @var array<mixed> */
    private readonly array $sorts;
    /** @var array<mixed> */
    private array $highlight = [];

    /** @var string|null free text search */
    private ?string $queryString = null;
    /** @var array<string, mixed> */
    private array $queryFacets = [];

    private int $page = 0;
    private int $size = 100;
    private ?string $sortBy = null;
    private string $analyzer;
    private string $sortOrder = 'asc';
    private readonly ?string $minimumShouldMatch;

    public function __construct(private readonly Request $request, ClientRequest $clientRequest)
    {
        $options = $this->getOptions($request, $clientRequest);

        if (isset($options['facets'])) {
            @\trigger_error('Deprecated facets, please use filters setting', E_USER_DEPRECATED);
        }

        if (isset($options['fields']) && isset($options['query_search'])) {
            throw new \RuntimeException('Cannot combine "fields" and "query" search config');
        }

        $this->indexRegex = $options['index_regex'] ?? null;
        $this->types = $options['types']; // required
        $this->querySearch = $options['query_search'] ?? null;
        $this->facets = $options['facets'] ?? [];
        $this->sizes = $options['sizes'] ?? [];
        $this->minimumShouldMatch = $options['minimum_should_match'] ?? null;
        $this->defaultSorts = $this->parseSorts($options['default_sorts'] ?? []);
        $this->sorts = $this->parseSorts($options['sorts'] ?? []);
        $this->fieldsExclude = $options['fields_exclude'] ?? ['*._content'];

        $this->setHighlight($options['highlight'] ?? []);
        $this->setFields($options['fields'] ?? []);
        $this->setSuggestFields($options['suggestFields'] ?? $options['fields'] ?? [], $clientRequest->getLocale());
        $this->setAnalyzer($options['analyzers'] ?? [
            'fr' => 'french',
            'nl' => 'dutch',
            'en' => 'english',
            'de' => 'german',
        ], $clientRequest->getLocale());
        $this->setSynonyms($options['synonyms'] ?? []);

        $filters = $options['filters'] ?? [];
        foreach ($filters as $name => $options) {
            $this->filters[$name] = new Filter($clientRequest, $name, $options);
        }

        $this->bindRequest($request);
    }

    private function bindRequest(Request $request): void
    {
        $this->queryString = $request->query->get('q', $request->get('q', $this->queryString));
        $requestFacets = $request->query->all()['f'] ?? $request->get('f', null);

        if (\is_array($requestFacets)) {
            $this->queryFacets = $requestFacets;
        }

        $this->page = \intval($request->query->get('p', $request->get('p', $this->page)));
        $this->setSize(\intval($request->query->get('l', $request->get('l', $this->size))));
        $this->setSortBy($request->query->get('s', $request->get('s')));
        $this->setSortOrder($request->query->all()['o'] ?? $request->get('o', $this->sortOrder));

        if (null !== $this->indexRegex) {
            $requestSearchIndex = RequestHelper::replace($request, $this->indexRegex);
            $this->indexRegex = RequestHelper::replace($request, $requestSearchIndex);
        }

        foreach ($this->filters as $filter) {
            $filter->handleRequest($request);
        }
    }

    public function bindAggregations(Response $response, ?AbstractQuery $queryFilters): void
    {
        foreach ($response->getAggregations() as $aggregation) {
            if ($this->hasFilter($aggregation->getName())) {
                $this->getFilter($aggregation->getName())->handleAggregation($aggregation->getRaw(), $this->getTypes(), $queryFilters);
            }
        }
    }

    public function getIndexRegex(): ?string
    {
        return $this->indexRegex;
    }

    /**
     * @return string[]
     */
    public function getTypes(): array
    {
        return $this->types;
    }

    /**
     * @return Synonym[]
     */
    public function getSynonyms(): array
    {
        return $this->synonyms;
    }

    /**
     * @return string[]
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    public function getQuerySearch(string $queryString): ?BoolQuery
    {
        if (null === $this->querySearch) {
            return null;
        }

        $jsonQuerySearch = Json::encode($this->querySearch);
        $jsonQuerySearch = u($jsonQuerySearch)->replace('%query%', Json::escape($queryString))->toString();
        $jsonQuerySearch = RequestHelper::replace($this->request, $jsonQuerySearch);

        $querySearch = Json::decode($jsonQuerySearch);

        if (!isset($querySearch['bool'])) {
            throw new \RuntimeException('Query search should be a bool query');
        }

        return (new BoolQuery())->setParams($querySearch['bool']);
    }

    public function getAnalyzer(): string
    {
        return $this->analyzer;
    }

    /**
     * @return string[]
     */
    public function getSuggestFields(): array
    {
        return $this->suggestFields;
    }

    public function hasQueryString(): bool
    {
        return null != $this->queryString;
    }

    public function getQueryString(): ?string
    {
        return $this->queryString ? QueryStringEscaper::escape($this->queryString) : null;
    }

    /**
     * @return array<string, mixed>
     */
    public function getQueryFacets(): array
    {
        $queryFacets = [];

        foreach ($this->queryFacets as $field => $terms) {
            if (\array_key_exists($field, $this->facets) && !empty($terms)) {
                $queryFacets[$field] = $terms;
            }
        }

        return $queryFacets;
    }

    public function hasFilter(string $name): bool
    {
        return isset($this->filters[$name]);
    }

    public function getFilter(string $name): Filter
    {
        return $this->filters[$name];
    }

    /**
     * @return Filter[]
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * @return Filter[]
     */
    public function getActiveFilters()
    {
        return \array_filter($this->filters, fn (Filter $filter) => $filter->isActive() && $filter->isPublic());
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getFrom(): int
    {
        return $this->page * $this->size;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * @return int[]
     */
    public function getSizes(): array
    {
        return $this->sizes;
    }

    /**
     * @return array<mixed>
     */
    public function getDefaultSorts(): array
    {
        return $this->defaultSorts;
    }

    /**
     * @return array<mixed>
     */
    public function getSort(): ?array
    {
        return $this->sorts[$this->sortBy] ?? null;
    }

    public function getSortBy(): ?string
    {
        return $this->sortBy;
    }

    public function getSortOrder(): string
    {
        return $this->sortOrder;
    }

    /**
     * @return array<string, mixed>
     */
    public function getSorts(): array
    {
        return $this->sorts;
    }

    /**
     * @return array<mixed>
     */
    public function getHighlight(): array
    {
        return $this->highlight;
    }

    public function getMinimumShouldMatch(): ?string
    {
        return $this->minimumShouldMatch;
    }

    /**
     * @return string[]
     */
    public function getFieldsExclude(): array
    {
        return $this->fieldsExclude;
    }

    /**
     * @return array<mixed>
     */
    private function getOptions(Request $request, ClientRequest $clientRequest): array
    {
        if ($requestSearchConfig = $request->get('search_config')) {
            if (\is_array($requestSearchConfig)) {
                return $requestSearchConfig;
            }
            @\trigger_error('Deprecated search_config as string, please define it as an object in your route\'s config', E_USER_DEPRECATED);

            return Json::decode($requestSearchConfig);
        }

        $currentEnvironment = $clientRequest->getCurrentEnvironment();

        if ($currentEnvironment && $currentEnvironment->hasOption('search_config')) {
            return $currentEnvironment->getOption('[search_config]');
        }

        if ($clientRequest->hasOption('search_config')) {
            return $clientRequest->getOption('[search_config]');
        }

        throw new \LogicException('no search defined!');
    }

    /**
     * @param array<string, array<mixed>|string> $sorts
     *
     * @return array<string, array<mixed>>
     */
    private function parseSorts(array $sorts): array
    {
        $result = [];

        foreach ($sorts as $name => $options) {
            if (\is_string($options)) {
                $options = ['field' => $options];
            }

            $options['field'] = RequestHelper::replace($this->request, $options['field']);

            if ('_score' !== $options['field']) {
                $options['missing'] = '_last';
            }

            $result[$name] = $options;
        }

        return $result;
    }

    /**
     * @param string[] $analyzers
     */
    private function setAnalyzer(array $analyzers, string $locale): void
    {
        $this->analyzer = $analyzers[$locale] ?? 'standard';
    }

    /**
     * @param string[] $fields
     */
    private function setFields(array $fields): void
    {
        $this->fields = \array_map(fn (string $field): string => RequestHelper::replace($this->request, $field), $fields);
    }

    /**
     * @param array<string, string[]> $suggestFields
     */
    private function setSuggestFields(array $suggestFields, string $locale): void
    {
        if (isset($suggestFields[$locale])) {
            $this->suggestFields = $suggestFields[$locale];
        } else {
            $this->suggestFields = [];
        }
    }

    /**
     * @param array<mixed> $data
     */
    private function setHighlight(array $data): void
    {
        if (\is_array($data) && isset($data['fields'])) {
            foreach ($data['fields'] as $key => $options) {
                $replacedKey = RequestHelper::replace($this->request, $key);
                if ($replacedKey !== $key) {
                    $data['fields'][$replacedKey] = $options;
                    unset($data['fields'][$key]);
                }
            }
            $this->highlight = $data;
        }
    }

    private function setSortBy(?string $name): void
    {
        if (null === $name) {
            return;
        }

        if (null == $this->sorts) {
            @\trigger_error('Define possible sort fields with the search option "sorts"', \E_USER_DEPRECATED);
        } elseif (\array_key_exists($name, $this->sorts)) {
            $this->sortBy = $name;
            $this->sortOrder = $this->sorts[$name]['order'] ?? $this->sortOrder;
        }
    }

    private function setSortOrder(string $o): void
    {
        $this->sortOrder = ('asc' === $o || 'desc' === $o) ? $o : 'asc';
    }

    private function setSize(int $l): void
    {
        if (null == $this->sizes) {
            @\trigger_error('Define allow sizes with the search option "sizes"', \E_USER_DEPRECATED);
            $this->size = \intval((int) $l > 0 ? $l : $this->size);
        } elseif (\in_array($l, $this->sizes)) {
            $this->size = (int) $l;
        } else {
            $this->size = (int) \reset($this->sizes);
        }
    }

    /**
     * @param array<mixed> $synonyms
     */
    private function setSynonyms(array $synonyms): void
    {
        foreach ($synonyms as $options) {
            if (\is_string($options)) {
                $options = ['types' => [$options]];
            }

            $this->synonyms[] = new Synonym($this->request, $options);
        }
    }
}
