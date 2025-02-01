<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\CoreApi\Endpoint\Admin;

use EMS\CommonBundle\Common\CoreApi\Client;
use EMS\CommonBundle\Contracts\CoreApi\Endpoint\Admin\ConfigInterface;

class Config implements ConfigInterface
{
    /** @var string[] */
    private readonly array $endPoint;

    public function __construct(private readonly Client $client, private readonly string $configType)
    {
        $this->endPoint = ['api', 'admin', $configType];
    }

    #[\Override]
    public function getType(): string
    {
        return $this->configType;
    }

    /**
     * @return string[]
     */
    #[\Override]
    public function index(): array
    {
        return $this->client->get(\implode('/', $this->endPoint))->getData();
    }

    /**
     * @return mixed[]
     */
    #[\Override]
    public function get(string $name): array
    {
        return $this->client->get(\implode('/', \array_merge($this->endPoint, [$name])))->getData();
    }

    #[\Override]
    public function update(string $name, array $data): string
    {
        $result = $this->client->post(\implode('/', \array_merge($this->endPoint, [$name])), $data);
        $id = $result->getData()['id'] ?? null;
        if (!\is_string($id)) {
            throw new \RuntimeException(\sprintf('Unexpected id type: %s', (string) $id));
        }

        return $id;
    }

    #[\Override]
    public function delete(string $name): string
    {
        $result = $this->client->delete(\implode('/', \array_merge($this->endPoint, [$name])));
        $id = $result->getData()['id'] ?? null;
        if (!\is_string($id)) {
            throw new \RuntimeException(\sprintf('Unexpected id type: %s', (string) $id));
        }

        return $id;
    }

    #[\Override]
    public function create(array $data): string
    {
        $result = $this->client->post(\implode('/', $this->endPoint), $data);
        $id = $result->getData()['id'] ?? null;
        if (!\is_string($id)) {
            throw new \RuntimeException(\sprintf('Unexpected id type: %s', (string) $id));
        }

        return $id;
    }
}
