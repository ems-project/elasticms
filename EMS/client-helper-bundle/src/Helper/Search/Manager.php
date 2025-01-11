<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Search;

use EMS\ClientHelperBundle\Helper\Elasticsearch\ClientRequest;
use EMS\ClientHelperBundle\Helper\Elasticsearch\ClientRequestManager;
use EMS\CommonBundle\Elasticsearch\Response\Response;
use Symfony\Component\HttpFoundation\Request;

final readonly class Manager
{
    private ClientRequest $clientRequest;

    public function __construct(ClientRequestManager $clientRequestManager)
    {
        $this->clientRequest = $clientRequestManager->getDefault();
    }

    public function getClientRequest(): ClientRequest
    {
        return $this->clientRequest;
    }

    /**
     * @return array<mixed>
     */
    public function search(Request $request): array
    {
        $requestSearch = new Search($request, $this->clientRequest);

        $qbService = new QueryBuilder($this->clientRequest, $requestSearch);
        $search = $qbService->buildSearch($requestSearch->getTypes());
        $search->setSourceExcludes($requestSearch->getFieldsExclude());
        $search->setFrom($requestSearch->getFrom());
        $search->setSize($requestSearch->getSize());
        $search->setRegex($requestSearch->getIndexRegex());

        $commonSearch = $this->clientRequest->commonSearch($search);
        $results = $commonSearch->getResponse()->getData();
        $results['hits']['total'] = $results['hits']['total']['value'] ?? $results['hits']['total'] ?? 0;

        $response = Response::fromResultSet($commonSearch);
        $requestSearch->bindAggregations($response, $qbService->getQueryFilters());

        return [
            'results' => $results,
            'response' => $response,
            'search' => $requestSearch,
            'query' => $requestSearch->getQueryString(),
            'sort' => $requestSearch->getSortBy(),
            'facets' => $requestSearch->getQueryFacets(),
            'page' => $requestSearch->getPage(),
            'size' => $requestSearch->getSize(),
        ];
    }
}
