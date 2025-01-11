<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\ContentType;

use Elastica\Aggregation\Max;
use Elastica\Aggregation\Terms;
use EMS\ClientHelperBundle\Helper\Elasticsearch\ClientRequest;
use EMS\ClientHelperBundle\Helper\Environment\Environment;
use EMS\CommonBundle\Elasticsearch\Response\Response;
use EMS\CommonBundle\Search\Search;

final class ContentTypeHelper
{
    /** @var ContentTypeCollection[] */
    private array $contentTypeCollections = [];

    public const string AGG_CONTENT_TYPE = 'contentType';
    public const string AGG_LAST_PUBLISHED = 'lastPublished';

    public function clear(): void
    {
        $this->contentTypeCollections = [];
    }

    public function get(ClientRequest $clientRequest, Environment $environment, string $contentTypeName): ?ContentType
    {
        return $this->getContentTypeCollection($clientRequest, $environment)->getByName($contentTypeName);
    }

    public function getContentTypeCollection(ClientRequest $clientRequest, Environment $environment): ContentTypeCollection
    {
        $environmentName = $environment->getName();
        if (!isset($this->contentTypeCollections[$environmentName])) {
            $this->contentTypeCollections[$environmentName] = $this->makeContentTypeCollection($clientRequest, $environment);
        }

        return $this->contentTypeCollections[$environmentName];
    }

    private function makeContentTypeCollection(ClientRequest $clientRequest, Environment $environment): ContentTypeCollection
    {
        try {
            $search = $this->createContentTypeSearch($environment);
            $response = Response::fromResultSet($clientRequest->commonSearch($search));

            return ContentTypeCollection::fromResponse($environment, $response);
        } catch (\Exception) {
            return new ContentTypeCollection();
        }
    }

    private function createContentTypeSearch(Environment $environment): Search
    {
        $maxUpdate = new Max(self::AGG_LAST_PUBLISHED);
        $maxUpdate->setField('_published_datetime');

        $lastUpdate = new Terms(self::AGG_CONTENT_TYPE);
        $lastUpdate->setField('_contenttype');
        $lastUpdate->setSize(100);
        $lastUpdate->addAggregation($maxUpdate);

        $search = new Search([$environment->getAlias()]);
        $search->setSize(0);
        $search->addAggregation($lastUpdate);

        return $search;
    }
}
