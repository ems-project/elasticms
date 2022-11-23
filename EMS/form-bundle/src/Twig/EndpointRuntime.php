<?php

declare(strict_types=1);

namespace EMS\FormBundle\Twig;

use EMS\FormBundle\Service\Endpoint\EndpointManager;
use Psr\Log\LoggerInterface;
use Twig\Extension\RuntimeExtensionInterface;

class EndpointRuntime implements RuntimeExtensionInterface
{
    private EndpointManager $endpointManager;
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger, EndpointManager $endpointManager)
    {
        $this->endpointManager = $endpointManager;
        $this->logger = $logger;
    }

    /**
     * @param array<string, string> $replaceBody
     *
     * @return array<string, mixed>|null
     */
    public function callHttpEndpoint(string $fieldName, array $replaceBody, int $timeout = 5): ?array
    {
        try {
            return $this->endpointManager->callHttpEndpoint($fieldName, $replaceBody, $timeout);
        } catch (\Throwable $e) {
            $this->logger->error('Error during the HTTP request', [
                'field' => $fieldName,
                'config' => $replaceBody,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
