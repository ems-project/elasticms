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
     * @param string[] $hosts
     */
    public function fromConfig(array $hosts): Client
    {
        $client = new Client(['hosts' => $hosts], $this->logger);

        if ($this->stopwatch) {
            $client->setStopwatch($this->stopwatch);
        }

        return $client;
    }
}
