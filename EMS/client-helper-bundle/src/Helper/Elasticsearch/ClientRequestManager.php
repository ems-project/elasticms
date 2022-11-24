<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Elasticsearch;

use EMS\ClientHelperBundle\Contracts\Elasticsearch\ClientRequestManagerInterface;
use Psr\Log\LoggerInterface;

final class ClientRequestManager implements ClientRequestManagerInterface
{
    /** @var array<string, ClientRequest> */
    private array $clientRequests = [];
    private ClientRequest $default;
    private LoggerInterface $logger;

    /**
     * @param iterable|ClientRequest[] $clientRequests
     */
    public function __construct(iterable $clientRequests, LoggerInterface $logger)
    {
        $this->logger = $logger;

        foreach ($clientRequests as $clientRequest) {
            $this->clientRequests[$clientRequest->getName()] = $clientRequest;

            if ($clientRequest->getOption('[default]', false)) {
                $this->default = $clientRequest;
            }
        }
    }

    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    public function getDefault(): ClientRequest
    {
        return $this->default;
    }

    /**
     * @return ClientRequest[]
     */
    public function all(): array
    {
        return $this->clientRequests;
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function get(string $name): ClientRequest
    {
        if (!isset($this->clientRequests[$name])) {
            throw new \InvalidArgumentException(\sprintf('Client request %s service not found!', $name));
        }

        return $this->clientRequests[$name];
    }
}
