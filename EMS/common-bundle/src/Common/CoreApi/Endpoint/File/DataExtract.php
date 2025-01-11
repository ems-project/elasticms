<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\CoreApi\Endpoint\File;

use EMS\CommonBundle\Common\CoreApi\Client;
use EMS\CommonBundle\Contracts\CoreApi\Endpoint\File\DataExtractInterface;

final readonly class DataExtract implements DataExtractInterface
{
    public function __construct(private Client $client)
    {
    }

    /**
     * @return array<string, mixed>
     */
    #[\Override]
    public function get(string $hash): array
    {
        $result = $this->client->get(\sprintf('/api/extract-data/get/%s', $hash));

        return $result->getData();
    }
}
