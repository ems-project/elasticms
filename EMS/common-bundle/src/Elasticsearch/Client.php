<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Elasticsearch;

use Elastica\Client as BaseClient;
use Elastica\Exception\ClientException;
use Elastica\Exception\ResponseException;
use Elastica\Request;
use Elastica\Response;
use EMS\Helpers\Standard\Json;
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
    #[\Override]
    public function request($path, $method = Request::GET, $data = [], array $query = [], $contentType = Request::DEFAULT_CONTENT_TYPE): Response
    {
        $this->stopwatch?->start('es_request', 'fos_elastica');

        try {
            $response = parent::request($path, $method, $data, $query, $contentType);
        } catch (ResponseException $e) {
            $this->getLogger()?->logResponse($e->getResponse(), $e->getRequest(), $e);
            throw $e;
        }
        $responseData = $response->getData();

        $transportInfo = $response->getTransferInfo();
        if (null === $lastRequest = $this->getLastRequest()) {
            return $response;
        }

        $connection = $lastRequest->getConnection();
        $forbiddenHttpCodes = $connection->hasConfig('http_error_codes') ? $connection->getConfig('http_error_codes') : [];

        if (isset($transportInfo['http_code']) && \is_array($forbiddenHttpCodes) && \in_array($transportInfo['http_code'], $forbiddenHttpCodes, true)) {
            $message = \sprintf('Error in transportInfo: response code is %s, response body is %s', $transportInfo['http_code'], Json::encode($responseData));
            throw new ClientException($message);
        }

        $this->getLogger()?->logResponse($response, $lastRequest);
        $this->stopwatch?->stop('es_request');

        return $response;
    }

    public function setStopwatch(?Stopwatch $stopwatch = null): void
    {
        $this->stopwatch = $stopwatch;
    }

    public function getLogger(): ?ElasticaLogger
    {
        return $this->_logger instanceof ElasticaLogger && $this->_logger->isEnabled() ? $this->_logger : null;
    }
}
