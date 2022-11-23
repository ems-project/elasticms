<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\CoreApi\Endpoint\File;

use EMS\CommonBundle\Common\CoreApi\Client;
use EMS\CommonBundle\Contracts\CoreApi\Endpoint\File\DataExtractInterface;

final class DataExtract implements DataExtractInterface
{
    private Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @return array<string, mixed>
     */
    public function get(string $hash): array
    {
        $result = $this->client->get(\sprintf('/api/extract-data/get/%s', $hash));

        return $result->getData();
    }
}
