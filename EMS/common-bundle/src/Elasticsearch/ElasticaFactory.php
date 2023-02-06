<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Elasticsearch;

use Elasticsearch\ConnectionPool\SniffingConnectionPool;
use Psr\Log\LoggerInterface;
use Symfony\Component\Stopwatch\Stopwatch;

class ElasticaFactory
{
    public function __construct(private readonly LoggerInterface $logger, private readonly string $env)
    {
    }

    /**
     * @param array<string> $hosts
     */
    public function fromConfig(array $hosts, ?string $connectionPool = null): Client
    {
        $servers = [];
        foreach ($hosts as $host) {
            if (!\str_ends_with($host, '/')) {
                $host .= '/';
            }
            $servers[] = ['url' => $host];
        }

        $config = [
            'servers' => $servers,
            'connectionPool' => $connectionPool ?? SniffingConnectionPool::class,
        ];

        $client = new Client($config);

        if ('dev' === $this->env && 'cli' !== \php_sapi_name()) {
            $client->setStopwatch(new Stopwatch());
            $client->setLogger($this->logger);
        }

        return $client;
    }
}
