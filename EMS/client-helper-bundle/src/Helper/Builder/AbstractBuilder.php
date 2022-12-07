<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Builder;

use EMS\ClientHelperBundle\Helper\ContentType\ContentType;
use EMS\ClientHelperBundle\Helper\Elasticsearch\ClientRequest;
use EMS\ClientHelperBundle\Helper\Elasticsearch\ClientRequestManager;
use EMS\ClientHelperBundle\Helper\Elasticsearch\Settings;
use EMS\ClientHelperBundle\Helper\Environment\Environment;
use EMS\CommonBundle\Elasticsearch\Response\Response;
use EMS\CommonBundle\Elasticsearch\Response\ResponseInterface;
use EMS\CommonBundle\Search\Search;
use Psr\Log\LoggerInterface;

/**
 * Abstract class for client builders.
 *
 * @see \EMS\ClientHelperBundle\Helper\Routing\RoutingBuilder
 * @see \EMS\ClientHelperBundle\Helper\Templating\TemplateBuilder
 * @see \EMS\ClientHelperBundle\Helper\Translation\TranslationBuilder
 */
abstract class AbstractBuilder
{
    protected ClientRequest $clientRequest;

    /**
     * @param string[] $locales
     */
    public function __construct(ClientRequestManager $manager, protected LoggerInterface $logger, protected array $locales, private readonly int $searchLimit)
    {
        $this->clientRequest = $manager->getDefault();
    }

    public function settings(Environment $environment): Settings
    {
        return $this->clientRequest->getSettings($environment);
    }

    protected function modifySearch(Search $search): void
    {
    }

    protected function search(ContentType $contentType): ResponseInterface
    {
        $search = new Search([$contentType->getEnvironment()->getAlias()]);
        $search->setContentTypes([$contentType->getName()]);
        $search->setSize($this->searchLimit);

        $this->modifySearch($search);

        $response = Response::fromResultSet($this->clientRequest->commonSearch($search));

        if ($response->getTotal() > $this->searchLimit) {
            $this->logger->error('Only the first {limit} {type}s have been loaded on a total of {total}, consider to raised that limit with emsch.search_limit', [
                'limit' => $this->searchLimit,
                'type' => $contentType->getName(),
                'total' => $response->getTotal(),
            ]);
        }

        return $response;
    }
}
