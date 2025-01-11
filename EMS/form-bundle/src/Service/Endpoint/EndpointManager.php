<?php

declare(strict_types=1);

namespace EMS\FormBundle\Service\Endpoint;

use EMS\FormBundle\Contracts\EndpointManagerInterface;
use EMS\FormBundle\Service\Confirmation\Endpoint\HttpEndpointType;
use EMS\Helpers\Standard\Json;
use Psr\Log\LoggerInterface;
use Twig\Extension\RuntimeExtensionInterface;

final class EndpointManager implements EndpointManagerInterface, RuntimeExtensionInterface
{
    /** @var EndpointInterface[] */
    private array $endpoints = [];

    /**
     * @param array<mixed>                         $config
     * @param \Traversable|EndpointTypeInterface[] $endpointTypes
     */
    public function __construct(private readonly array $config, private readonly \Traversable $endpointTypes, private readonly LoggerInterface $logger)
    {
    }

    public function getEndpointType(EndpointInterface $endpoint): EndpointTypeInterface
    {
        foreach ($this->endpointTypes as $endpointType) {
            if ($endpointType->canExecute($endpoint)) {
                return $endpointType;
            }
        }

        throw new \Exception(\sprintf('Endpoint type "%s" not found!', $endpoint->getType()));
    }

    #[\Override]
    public function getEndpointByFieldName(string $fieldName): EndpointInterface
    {
        foreach ($this->loadEndpoints() as $endpoint) {
            if ($fieldName === $endpoint->getFieldname()) {
                return $endpoint;
            }
        }

        throw new \Exception(\sprintf('No endpoint found for form field %s', $fieldName));
    }

    /**
     * @param array<string, string> $replaceBody
     *
     * @return array<string, mixed>
     */
    public function callHttpEndpoint(string $fieldName, array $replaceBody, int $timeout = 5): array
    {
        $endpoint = $this->getEndpointByFieldName($fieldName);
        $httpEndpoint = $this->getEndpointType($endpoint);
        if (!$httpEndpoint instanceof HttpEndpointType) {
            throw new \RuntimeException('Unexpected non HTTP endpoint');
        }
        $response = $httpEndpoint->request($endpoint, $replaceBody, $timeout);
        $result = Json::decode($response->getContent());

        return $result;
    }

    /**
     * @return EndpointInterface[]
     */
    private function loadEndpoints(): array
    {
        if (\count($this->endpoints) > 0) {
            return $this->endpoints;
        }

        foreach ($this->config as $config) {
            try {
                $this->endpoints[] = new Endpoint($config);
            } catch (\Exception $e) {
                $this->logger->error('invalid endpoint configuration', [
                    'config' => $config,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $this->endpoints;
    }
}
