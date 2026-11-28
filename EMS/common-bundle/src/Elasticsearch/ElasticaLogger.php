<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Elasticsearch;

use Elastica\Response;
use EMS\CommonBundle\Contracts\Elasticsearch\QueryLoggerInterface;
use EMS\Helpers\Standard\Json;
use Psr\Http\Message\RequestInterface;
use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;

class ElasticaLogger extends AbstractLogger implements QueryLoggerInterface
{
    /** @var array<mixed> */
    private array $queries = [];
    private bool $enabled = true;

    public function __construct(
        private readonly ?LoggerInterface $logger = null,
        private readonly bool $debug = false,
    ) {
    }

    #[\Override]
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    #[\Override]
    public function disable(): void
    {
        $this->enabled = false;
    }

    #[\Override]
    public function enable(): void
    {
        $this->enabled = true;
    }

    public function getNbQueries(): int
    {
        return \count($this->queries);
    }

    /**
     * @return array<mixed>
     */
    public function getQueries(): array
    {
        return $this->queries;
    }

    #[\Override]
    public function log($level, string|\Stringable $message, array $context = []): void
    {
        if (null !== $this->logger && $this->isEnabled()) {
            $this->logger->log($level, $message, $context);
        }
    }

    public function logResponse(RequestInterface $request, Response $response, float $queryTime = 0.0): void
    {
        $executionMS = $queryTime * 1000;
        $uri = $request->getUri();
        $path = \ltrim($uri->getPath(), '/');
        try {
            $data = \json_decode((string) $request->getBody(), true, 512, \JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            $data = [];
        }

        $query = [];
        \parse_str($request->getUri()->getQuery(), $query);

        if ($this->debug) {
            if (\is_string($data)) {
                $jsonStrings = \explode("\n", $data);
                $data = \array_filter(\array_map(static fn ($v) => Json::isJson($v) ? Json::decode($v) : null, $jsonStrings));
            } else {
                $data = [$data];
            }
            $responseData = $response->getData();

            $this->queries[] = [
                'path' => $path,
                'method' => $request->getMethod(),
                'data' => $data,
                'executionMS' => $queryTime * 1000,
                'engineMS' => (isset($responseData['took']) ? $response->getEngineTime() : 0),
                'error' => $response->hasError() ? $response->getError() : null,
                'connection' => [
                    'host' => $uri->getHost(),
                    'port' => $uri->getPort(),
                    'transport' => $uri->getScheme(),
                    'headers' => $request->getHeaders(),
                ],
                'queryString' => $query,
                'itemCount' => ($responseData['hits']['total']['value'] ?? 0),
                'backtrace' => new \Exception()->getTraceAsString(),
            ];
        }

        if (null !== $this->logger) {
            $message = \sprintf('%s (%s) %0.2f ms', $uri->getPath(), $request->getMethod(), $executionMS);
            $this->logger->info($message, (array) $data);
        }
    }

    public function reset(): void
    {
        $this->queries = [];
    }
}
