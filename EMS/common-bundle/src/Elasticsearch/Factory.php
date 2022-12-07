<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Elasticsearch;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Elasticsearch\ConnectionPool\SniffingConnectionPool;
use Psr\Log\LoggerInterface;

class Factory
{
    public function __construct(private readonly LoggerInterface $logger, private readonly string $env)
    {
    }

    /**
     * @param array<mixed> $config
     */
    public function fromConfig(array $config): Client
    {
        if ('dev' === $this->env && 'cli' !== \php_sapi_name()) {
            // for performance reason only in dev mode: https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/_configuration.html#enabling_logger
            $config['Tracer'] = $this->logger;
        }
        $config['connectionPool'] = SniffingConnectionPool::class;

        return ClientBuilder::fromConfig($config);
    }
}
