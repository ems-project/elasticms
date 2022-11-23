<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Elasticsearch;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Elasticsearch\ConnectionPool\SniffingConnectionPool;
use Psr\Log\LoggerInterface;

class Factory
{
    /** @var LoggerInterface */
    private $logger;
    /** @var string */
    private $env;

    public function __construct(LoggerInterface $logger, string $env)
    {
        $this->logger = $logger;
        $this->env = $env;
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
