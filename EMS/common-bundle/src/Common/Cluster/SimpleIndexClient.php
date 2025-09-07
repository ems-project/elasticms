<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\Cluster;

use EMS\CommonBundle\Common\HttpClientFactory;
use EMS\Helpers\Standard\Json;
use EMS\Helpers\Standard\Type;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class SimpleIndexClient
{
    private Client $client;
    private bool $defined;
    private string $index;
    /** @var mixed[] */
    private array $mappings;

    /**
     * @param array<mixed> $headers
     */
    private function __construct(public readonly string $baseUrl, public readonly array $headers = [])
    {
        $this->client = HttpClientFactory::create($baseUrl, $headers);
    }

    /**
     * @param array<mixed> $headers
     */
    public static function create(string $baseUrl, array $headers = []): self
    {
        $indexClient = new self($baseUrl, $headers);
        try {
            $response = Json::decode($indexClient->client->request('GET', '')->getBody()->getContents());
            $indexClient->defined = true;
        } catch (RequestException $e) {
            if (404 !== $e->getCode()) {
                throw $e;
            }
            $indexClient->defined = false;

            return $indexClient;
        }
        if (1 !== \count($response)) {
            throw new \RuntimeException(\sprintf('SimpleIndexClient does not support multiple indexes alias. This alias contains %d indexes', \count($response)));
        }
        $indexClient->index = Type::string(\array_key_first($response));
        $indexClient->mappings = $response[$indexClient->index]['mappings'];

        return $indexClient;
    }

    public function isDefined(): bool
    {
        return $this->defined;
    }

    public function getIndex(): string
    {
        return $this->index;
    }

    /**
     * @return array<mixed>
     */
    public function getMappings(): array
    {
        return $this->mappings;
    }
}
