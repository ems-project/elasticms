<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Elasticsearch;

use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use Elastic\Elasticsearch\Response\Elasticsearch;
use Elastica\Client as BaseClient;
use Elastica\Response;
use Http\Promise\Promise;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Stopwatch\Stopwatch;

class Client extends BaseClient
{
    private ?Stopwatch $stopwatch = null;

    /**
     * @param string[]                 $headers
     * @param array<mixed>|string|null $body
     */
    public function request(string $method, string $url, array $headers, array|string|null $body = null): Response
    {
        try {
            $request = $this->createRequest($method, $url, $headers, $body);

            return $this->resolveResponse($this->sendRequest($request));
        } catch (\Throwable $throwable) {
            if ($throwable instanceof ClientResponseException || $throwable instanceof ServerResponseException) {
                return $this->toElasticaResponse($throwable->getResponse());
            }
            throw $throwable;
        }
    }

    public function resolveResponse(Elasticsearch|Promise $response): Response
    {
        if ($response instanceof Promise) {
            $response->wait();
        }

        if (!$response instanceof ResponseInterface) {
            throw new \RuntimeException('Unexpected response type');
        }

        return $this->toElasticaResponse($response);
    }

    #[\Override]
    public function sendRequest(RequestInterface $request): Elasticsearch
    {
        $this->stopwatch?->start('es_request', 'elastica');
        $start = \microtime(true);

        try {
            $elasticResponse = parent::sendRequest($request);
            $response = $this->toElasticaResponse($elasticResponse);
        } catch (\Throwable $throwable) {
            if ($throwable instanceof ClientResponseException || $throwable instanceof ServerResponseException) {
                $this->logResponse($this->toElasticaResponse($throwable->getResponse()));
            }
            $this->getLogger()->error($throwable->getMessage());
            throw $throwable;
        }
        $end = \microtime(true);
        $this->stopwatch?->stop('es_request');

        $this->logResponse($response, $end - $start);

        return $elasticResponse;
    }

    public function setStopwatch(?Stopwatch $stopwatch = null): void
    {
        $this->stopwatch = $stopwatch;
    }

    private function logResponse(Response $response, float $queryTime = 0.0): void
    {
        if (!$this->_logger instanceof ElasticaLogger) {
            return;
        }

        if (null === $request = $this->getTransport()->getLastRequest()) {
            return;
        }

        $this->_logger->logResponse($request, $response, $queryTime);
    }
}
