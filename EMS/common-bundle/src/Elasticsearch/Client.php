<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Elasticsearch;

use Elastica\Client as BaseClient;
use Elastica\Exception\ClientException;
use Elastica\Request;
use Elastica\Response;
use Symfony\Component\Stopwatch\Stopwatch;

class Client extends BaseClient
{
    private ?Stopwatch $stopwatch = null;

    /**
     * @param string              $path
     * @param array<mixed>|string $data
     * @param string              $contentType
     * @param array<mixed>        $query
     * @param string              $contentType
     */
    public function request($path, $method = Request::GET, $data = [], array $query = [], $contentType = Request::DEFAULT_CONTENT_TYPE): Response
    {
        $this->stopwatch?->start('es_request', 'fos_elastica');

        $response = parent::request($path, $method, $data, $query, $contentType);
        $responseData = $response->getData();

        $transportInfo = $response->getTransferInfo();
        if (null === $lastRequest = $this->getLastRequest()) {
            return $response;
        }

        $connection = $lastRequest->getConnection();
        $forbiddenHttpCodes = $connection->hasConfig('http_error_codes') ? $connection->getConfig('http_error_codes') : [];

        if (isset($transportInfo['http_code']) && \is_array($forbiddenHttpCodes) && \in_array($transportInfo['http_code'], $forbiddenHttpCodes, true)) {
            $message = \sprintf('Error in transportInfo: response code is %s, response body is %s', $transportInfo['http_code'], \json_encode($responseData, JSON_THROW_ON_ERROR));
            throw new ClientException($message);
        }

        $this->log($response, $lastRequest);
        $this->stopwatch?->stop('es_request');

        return $response;
    }

    public function setStopwatch(?Stopwatch $stopwatch = null): void
    {
        $this->stopwatch = $stopwatch;
    }

    private function log(Response $response, Request $request): void
    {
        if (!$this->_logger instanceof ElasticaLogger || !$this->_logger->isEnabled()) {
            return;
        }

        $this->_logger->logResponse($response, $request);
    }
}
