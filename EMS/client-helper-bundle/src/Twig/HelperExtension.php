<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Twig;

use EMS\ClientHelperBundle\Exception\SingleResultException;
use EMS\ClientHelperBundle\Helper\Elasticsearch\ClientRequestManager;
use EMS\ClientHelperBundle\Helper\Search\Manager;
use EMS\ClientHelperBundle\Helper\Search\Search;
use EMS\ClientHelperBundle\Helper\Webhook\Webhook;
use EMS\ClientHelperBundle\Helper\Webhook\WebhookHelper;
use EMS\CommonBundle\Common\EMSLink;
use EMS\CommonBundle\Elasticsearch\Document\Document;
use EMS\CommonBundle\Elasticsearch\Document\DocumentInterface;
use EMS\CommonBundle\Elasticsearch\Exception\NotFoundException;
use EMS\CommonBundle\Elasticsearch\Exception\NotSingleResultException;
use EMS\CommonBundle\Service\ElasticaService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig\Attribute\AsTwigFilter;
use Twig\Attribute\AsTwigFunction;

class HelperExtension
{
    /** @var DocumentInterface[] */
    private array $documents = [];

    public function __construct(
        private readonly ClientRequestManager $manager,
        private readonly RequestStack $requestStack,
        private readonly LoggerInterface $logger,
        private readonly ElasticaService $elasticaService,
        private readonly Manager $searchManager,
        private readonly WebhookHelper $webhookHelper
    ) {
    }

    /**
     * @param mixed[] $config
     */
    #[AsTwigFunction(name: 'emsch_add_environment')]
    public function addEnvironment(string $name, array $config = [], string $website = 'website'): void
    {
        $clientRequest = $this->manager->get($website);
        $clientRequest->addEnvironment($name, $config);
    }

    /**
     * @param string[] $source
     */
    #[AsTwigFilter(name: 'emsch_get')]
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

    #[AsTwigFunction(name: 'emsch_webhook_event')]
    public function getWebhook(): Webhook
    {
        return $this->webhookHelper->getWebhook();
    }

    /**
     * @param mixed[] $headers
     */
    #[AsTwigFunction(name: 'emsch_http_error')]
    public function httpException(int $statusCode, ?string $message = null, array $headers = [], int $code = 0): never
    {
        if (null === $message) {
            $message = SymfonyResponse::$statusTexts[$statusCode] ?? 'Unknown status';
        }
        throw new HttpException($statusCode, $message, null, $headers, $code);
    }

    /**
     * @param string|string[]|null $type
     * @param array<mixed>         $body
     *
     * @return array<mixed>
     */
    #[AsTwigFunction(name: 'emsch_search')]
    public function search(string|array|null $type, array $body, int $from = 0, int $size = 10, ?string $regex = null, ?string $index = null, bool $cache = false): array
    {
        $client = $this->manager->getDefault();

        return $client->search($type, $body, $from, $size, $regex, $index, $cache);
    }

    /**
     * @param array<mixed> $options
     */
    #[AsTwigFunction(name: 'emsch_search_config')]
    public function searchConfig(array $options): Search
    {
        $currentRequest = $this->requestStack->getCurrentRequest();
        if (null === $currentRequest) {
            throw new \RuntimeException('Unexpected null request');
        }

        return new Search($currentRequest, $this->manager->getDefault(), $options);
    }

    /**
     * @return array<mixed>
     */
    #[AsTwigFunction(name: 'emsch_search_config_execute')]
    public function searchConfigExecute(Search $searchConfig): array
    {
        return $this->searchManager->search($searchConfig);
    }

    /**
     * @param string|string[]|null $type
     * @param array<mixed>         $body
     */
    #[AsTwigFunction(name: 'emsch_search_one')]
    public function searchOne(string|array|null $type, array $body, ?string $indexRegex = null): DocumentInterface
    {
        try {
            return Document::fromArray($this->manager->getDefault()->searchOne($type, $body, $indexRegex));
        } catch (SingleResultException) {
            throw new NotFoundHttpException('Page not found');
        }
    }
}
