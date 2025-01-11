<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Search;

use Elastica\Query\AbstractQuery;
use Elastica\Query\BoolQuery;
use Elastica\Query\Terms;
use EMS\ClientHelperBundle\Helper\Request\RequestHelper;
use EMS\CommonBundle\Elasticsearch\Document\EMSSource;
use Symfony\Component\HttpFoundation\Request;

final class Synonym
{
    /** @var list<string> */
    private readonly array $types;
    private ?string $field = null;
    private ?string $searchField = null;
    /** @var array<mixed> */
    private readonly array $filter;

    /**
     * @param array{types?: ?string[], field?: ?string, search?: ?string, filter?: ?array<mixed>} $data
     */
    public function __construct(Request $request, array $data)
    {
        $this->types = \array_values($data['types'] ?? []);
        $this->filter = $data['filter'] ?? [];

        if (isset($data['field'])) {
            $this->field = RequestHelper::replace($request, $data['field']);
        }

        if (isset($data['search'])) {
            $this->searchField = RequestHelper::replace($request, $data['search']);
        }
    }

    public function getSearchField(): string
    {
        return $this->searchField ?? '_all';
    }

    public function getField(): string
    {
        return $this->field ?? '_all';
    }

    public function getQuery(AbstractQuery $queryTextValue): AbstractQuery
    {
        $query = new BoolQuery();
        $query->addMust($queryTextValue);

        if (\count($this->types) > 0) {
            $terms = new Terms(EMSSource::FIELD_CONTENT_TYPE, $this->types);
            $query->addMust($terms);
        }

        if (\count($this->filter) > 0) {
            $query->addMust($this->filter);
        }

        return $query;
    }
}
