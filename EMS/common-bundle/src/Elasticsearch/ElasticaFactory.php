<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Elasticsearch;

use Symfony\Component\Stopwatch\Stopwatch;

class ElasticaFactory
{
    public function __construct(
        private readonly ElasticaLogger $logger,
        private readonly ?Stopwatch $stopwatch = null,
    ) {
    }

    /**
     * @param mixed[] $config
     */
    public function fromConfig(array $config): Client
    {
        if (isset($config['hosts'])) {
            $client = new Client($config, $this->logger);
        } else {
            $client = new Client(['hosts' => $config], $this->logger);
        }

        if ($this->stopwatch instanceof Stopwatch) {
            $client->setStopwatch($this->stopwatch);
        }

        return $client;
    }
}
