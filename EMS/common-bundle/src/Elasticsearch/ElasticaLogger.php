<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Elasticsearch;

use Elastica\Request;
use Elastica\Response;
use EMS\CommonBundle\Contracts\Elasticsearch\QueryLoggerInterface;
use EMS\Helpers\Standard\Json;
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

    public function logResponse(Response $response, Request $request): void
    {
        $responseData = $response->getData();
        $queryTime = $response->getQueryTime();
        $connection = $request->getConnection();
        $data = $request->getData();

        $executionMS = $queryTime * 1000;

        if ($this->debug) {
            if (\is_string($data)) {
                $jsonStrings = \explode("\n", $data);
                $data = \array_filter(\array_map(static fn ($v) => Json::isJson($v) ? Json::decode($v) : null, $jsonStrings));
            } else {
                $data = [$data];
            }

            $this->queries[] = [
                'path' => $request->getPath(),
                'method' => $request->getMethod(),
                'data' => $data,
                'executionMS' => $executionMS,
                'engineMS' => $responseData['took'] ?? 0,
                'error' => $response->getFullError(),
                'connection' => [
                    'host' => $connection->getHost(),
                    'port' => $connection->getPort(),
                    'transport' => $connection->getTransport(),
                    'headers' => $connection->hasConfig('headers') ? $connection->getConfig('headers') : [],
                ],
                'queryString' => $request->getQuery(),
                'itemCount' => $responseData['hits']['total']['value'] ?? $responseData['hits']['total'] ?? 0,
                'backtrace' => (new \Exception())->getTraceAsString(),
            ];
        }

        if (null !== $this->logger) {
            $message = \sprintf('%s (%s) %0.2f ms', $request->getPath(), $request->getMethod(), $executionMS);
            $this->logger->info($message, (array) $data);
        }
    }

    public function reset(): void
    {
        $this->queries = [];
    }
}
