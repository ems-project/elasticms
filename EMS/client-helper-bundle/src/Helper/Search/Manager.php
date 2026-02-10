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
    public function searchFromRequest(Request $request): array
    {
        return $this->search(new Search($request, $this->clientRequest));
    }

    /**
     * @return array<mixed>
     */
    public function search(Search $searchConfig): array
    {
        $qbService = new QueryBuilder($this->clientRequest, $searchConfig);

        $search = $qbService->buildSearch($searchConfig->getTypes());
        $search->setSourceExcludes($searchConfig->getFieldsExclude());
        $search->setFrom($searchConfig->getFrom());
        $search->setSize($searchConfig->getSize());
        $search->setRegex($searchConfig->getIndexRegex());

        $commonSearch = $this->clientRequest->commonSearch($search);
        /** @var array{ 'hits': array<mixed> } $results */
        $results = $commonSearch->getResponse()->getData();
        $results['hits']['total'] = $results['hits']['total']['value'] ?? $results['hits']['total'] ?? 0;

        $response = Response::fromResultSet($commonSearch);
        $searchConfig->bindAggregations($response, $qbService->getQueryFilters());

        return [
            'results' => $results,
            'response' => $response,
            'search' => $searchConfig,
            'query' => $searchConfig->getQueryString(),
            'sort' => $searchConfig->getSortBy(),
            'page' => $searchConfig->getPage(),
            'size' => $searchConfig->getSize(),
        ];
    }
}
