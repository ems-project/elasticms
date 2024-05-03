<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Elasticsearch;

use EMS\ClientHelperBundle\Exception\SingleResultException;
use EMS\ClientHelperBundle\Helper\Search\Search;
use EMS\CommonBundle\Common\EMSLink;
use EMS\CommonBundle\Elasticsearch\Document\Document;
use EMS\CommonBundle\Elasticsearch\Document\DocumentInterface;
use EMS\CommonBundle\Elasticsearch\Exception\NotFoundException;
use EMS\CommonBundle\Elasticsearch\Exception\NotSingleResultException;
use EMS\CommonBundle\Elasticsearch\Response\Response;
use EMS\CommonBundle\Service\ElasticaService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig\Extension\RuntimeExtensionInterface;

final class ClientRequestRuntime implements RuntimeExtensionInterface
{
    /** @var DocumentInterface[] */
    private array $documents = [];

    public function __construct(
        private readonly ClientRequestManager $manager,
        private readonly RequestStack $requestStack,
        private readonly LoggerInterface $logger,
        private readonly ElasticaService $elasticaService,
    ) {
    }

    /**
     * @param string|string[]|null $type
     * @param array<mixed>         $body
     * @param string[]             $sourceExclude
     *
     * @return array<mixed>
     */
    public function search(string|array|null $type, array $body, int $from = 0, int $size = 10, array $sourceExclude = [], ?string $regex = null, ?string $index = null): array
    {
        $client = $this->manager->getDefault();

        return $client->search($type, $body, $from, $size, $sourceExclude, $regex, $index);
    }

    /**
     * @param string|string[]|null $type
     * @param array<mixed>         $body
     */
    public function searchOne(string|array|null $type, array $body, ?string $indexRegex = null): DocumentInterface
    {
        try {
            return Document::fromArray($this->manager->getDefault()->searchOne($type, $body, $indexRegex));
        } catch (SingleResultException) {
            throw new NotFoundHttpException('Page not found');
        }
    }

    /**
     * @param mixed[] $headers
     */
    public function httpException(int $statusCode, string $message = '', array $headers = [], int $code = 0): never
    {
        throw new HttpException($statusCode, $message, null, $headers, $code);
    }

    public function searchConfig(): Search
    {
        $currentRequest = $this->requestStack->getCurrentRequest();
        if (null === $currentRequest) {
            throw new \RuntimeException('Unexpected null request');
        }

        return new Search($currentRequest, $this->manager->getDefault());
    }

    /**
     * @return mixed
     */
    public function data(string $input)
    {
        @\trigger_error(\sprintf('The filter emsch_data is deprecated and should not be used anymore. use the filter emsch_get instead"'), E_USER_DEPRECATED);

        $emsLink = EMSLink::fromText($input);
        $body = [
            'query' => [
                'bool' => [
                    'must' => [['term' => ['_id' => $emsLink->getOuuid()]]],
                ],
            ],
        ];

        if ($emsLink->hasContentType()) {
            $body['query']['bool']['must'][] = [
                'term' => ['_contenttype' => $emsLink->getContentType()],
            ];
        }

        $results = $this->manager->getDefault()->searchArgs(['body' => $body]);
        $response = Response::fromArray($results);

        if (1 === $response->getTotal()) {
            return $results['hits']['hits'];
        }

        return ($response->getTotal() > 1) ? false : null;
    }

    /**
     * @param string[] $source
     */
    public function get(string $input, array $source = []): ?DocumentInterface
    {
        $emsLink = EMSLink::fromText($input);

        if (isset($this->documents[$emsLink->__toString()])) {
            return $this->documents[$emsLink->__toString()];
        }

        try {
            $document = $this->elasticaService->getDocument($this->manager->getDefault()->getAlias(), $emsLink->hasContentType() ? $emsLink->getContentType() : null, $emsLink->getOuuid(), $source);
            $this->documents[$emsLink->__toString()] = $document;

            return $document;
        } catch (NotSingleResultException $e) {
            $this->logger->error(\sprintf('emsch_get filter found %d results for the ems key %s', $e->getTotal(), $input));
            $resultSet = $e->getResultSet();
            if (0 === $e->getTotal() || null === $resultSet) {
                return null;
            }
            $document = Document::fromResult($resultSet->offsetGet(0));
            $this->documents[$emsLink->__toString()] = $document;

            return $document;
        } catch (NotFoundException) {
            return null;
        }
    }

    /**
     * @param mixed[] $config
     */
    public function addEnvironment(string $name, array $config = [], string $website = 'website'): void
    {
        $clientRequest = $this->manager->get($website);
        $clientRequest->addEnvironment($name, $config);
    }
}
