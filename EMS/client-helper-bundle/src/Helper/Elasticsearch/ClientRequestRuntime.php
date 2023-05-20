<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Elasticsearch;

use EMS\ClientHelperBundle\Exception\SingleResultException;
use EMS\ClientHelperBundle\Helper\Search\Search;
use EMS\CommonBundle\Common\EMSLink;
use EMS\CommonBundle\Elasticsearch\Document\Document;
use EMS\CommonBundle\Elasticsearch\Document\DocumentInterface;
use EMS\CommonBundle\Elasticsearch\Response\Response;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig\Extension\RuntimeExtensionInterface;

final class ClientRequestRuntime implements RuntimeExtensionInterface
{
    /** @var DocumentInterface[] */
    private array $documents = [];

    public function __construct(private readonly ClientRequestManager $manager, private readonly RequestStack $requestStack, private readonly LoggerInterface $logger)
    {
    }

    /**
     * @param string|string[]|null $type
     * @param array<mixed>         $body
     * @param string[]             $sourceExclude
     *
     * @return array<mixed>
     */
    public function search(null|string|array $type, array $body, int $from = 0, int $size = 10, array $sourceExclude = [], ?string $regex = null, ?string $index = null): array
    {
        $client = $this->manager->getDefault();

        return $client->search($type, $body, $from, $size, $sourceExclude, $regex, $index);
    }

    /**
     * @param string|string[]|null $type
     * @param array<mixed>         $body
     */
    public function searchOne(null|string|array $type, array $body, ?string $indexRegex = null): DocumentInterface
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
    public function httpException(int $statusCode, ?string $message = '', array $headers = [], ?int $code = 0): never
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
            $body['query']['bool']['should'] = [
                ['term' => ['_type' => $emsLink->getContentType()]],
                ['term' => ['_contenttype' => $emsLink->getContentType()]],
                ['term' => ['contenttype' => $emsLink->getContentType()]],
            ];
        }

        $results = $this->manager->getDefault()->searchArgs(['body' => $body]);
        $response = Response::fromArray($results);

        if (1 === $response->getTotal()) {
            return $results['hits']['hits'];
        }

        return ($response->getTotal() > 1) ? false : null;
    }

    public function get(string $input): ?DocumentInterface
    {
        $emsLink = EMSLink::fromText($input);

        if (isset($this->documents[$emsLink->__toString()])) {
            return $this->documents[$emsLink->__toString()];
        }

        $bool = ['must' => [['term' => ['_id' => $emsLink->getOuuid()]]]];

        if ($emsLink->hasContentType()) {
            $bool['minimum_should_match'] = 1;
            $bool['should'] = [
                ['term' => ['_type' => $emsLink->getContentType()]],
                ['term' => ['_contenttype' => $emsLink->getContentType()]],
            ];
        }

        $result = $this->manager->getDefault()->searchArgs(['body' => ['query' => ['bool' => $bool]]]);
        $total = $result['hits']['total']['value'] ?? $result['hits']['total'];

        if (0 === $total) {
            return null;
        }

        if (1 !== $total) {
            $this->logger->error(\sprintf('emsch_get filter found %d results for the ems key %s', $total, $input));
        }

        $document = Document::fromArray($result['hits']['hits'][0]);
        $this->documents[$emsLink->__toString()] = $document;

        return $document;
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
