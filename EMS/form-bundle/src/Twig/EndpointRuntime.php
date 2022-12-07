<?php

declare(strict_types=1);

namespace EMS\FormBundle\Twig;

use EMS\FormBundle\Service\Endpoint\EndpointManager;
use Psr\Log\LoggerInterface;
use Twig\Extension\RuntimeExtensionInterface;

class EndpointRuntime implements RuntimeExtensionInterface
{
    public function __construct(private readonly LoggerInterface $logger, private readonly EndpointManager $endpointManager)
    {
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
