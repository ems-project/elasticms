<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Elasticsearch;

use EMS\CommonBundle\Contracts\Elasticsearch\QueryLoggerInterface;
use EMS\Helpers\Standard\Json;
use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;

class ElasticaLogger extends AbstractLogger implements QueryLoggerInterface
{
    /** @var array<mixed> */
    private array $queries = [];
    private bool $enabled = true;

    public function __construct(private readonly ?LoggerInterface $logger = null, private readonly bool $debug = false)
    {
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function disable(): void
    {
        $this->enabled = false;
    }

    public function enable(): void
    {
        $this->enabled = true;
    }

    /**
     * @param array<mixed>|string $data       Arguments
     * @param array<mixed>        $connection Host, port, transport, and headers of the query
     * @param array<mixed>        $query      Arguments
     */
    public function logQuery(string $path, string $method, array|string $data, float $queryTime, array $connection = [], array $query = [], int $engineTime = 0, int $itemCount = 0): void
    {
        $executionMS = $queryTime * 1000;

        if ($this->debug) {
            if (\is_string($data)) {
                $jsonStrings = \explode("\n", $data);
                $data = [];
                foreach ($jsonStrings as $json) {
                    if ('' != $json) {
                        $data[] = Json::decode($json);
                    }
                }
            } else {
                $data = [$data];
            }

            $this->queries[] = [
                'path' => $path,
                'method' => $method,
                'data' => $data,
                'executionMS' => $executionMS,
                'engineMS' => $engineTime,
                'connection' => $connection,
                'queryString' => $query,
                'itemCount' => $itemCount,
                'backtrace' => (new \Exception())->getTraceAsString(),
            ];
        }

        if (null !== $this->logger) {
            $message = \sprintf('%s (%s) %0.2f ms', $path, $method, $executionMS);
            $this->logger->info($message, (array) $data);
        }
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

    /**
     * @param array<mixed> $context
     */
    public function log($level, $message, array $context = []): void
    {
        if (null !== $this->logger && $this->isEnabled()) {
            $this->logger->log($level, $message, $context);
        }
    }

    public function reset(): void
    {
        $this->queries = [];
    }
}
