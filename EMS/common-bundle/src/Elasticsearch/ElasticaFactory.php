<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Elasticsearch;

use Elasticsearch\ConnectionPool\SniffingConnectionPool;
use Psr\Log\LoggerInterface;
use Symfony\Component\Stopwatch\Stopwatch;

class ElasticaFactory
{
    private LoggerInterface $logger;
    private string $env;

    public function __construct(LoggerInterface $logger, string $env)
    {
        $this->logger = $logger;
        $this->env = $env;
    }

    /**
     * @param array<string> $hosts
     */
    public function fromConfig(array $hosts, string $connectionPool = SniffingConnectionPool::class): Client
    {
        $servers = [];
        foreach ($hosts as $host) {
            if ('/' !== \substr($host, -1)) {
                $host .= '/';
            }
            $servers[] = ['url' => $host];
        }

        $config = [
            'servers' => $servers,
            'connectionPool' => $connectionPool,
        ];

        $client = new Client($config);

        if ('dev' === $this->env && 'cli' !== \php_sapi_name()) {
            $client->setStopwatch(new Stopwatch());
            $client->setLogger($this->logger);
        }

        return $client;
    }
}
