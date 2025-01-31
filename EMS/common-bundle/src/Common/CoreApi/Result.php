<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\CoreApi;

use EMS\Helpers\Standard\Json;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class Result
{
    private readonly ?bool $acknowledged;
    private readonly ?bool $success;
    /** @var array<mixed> */
    private readonly array $data;

    public function __construct(
        public readonly ResponseInterface $response,
        LoggerInterface $logger,
    ) {
        $data = Json::decode($response->getContent());
        $this->data = $data;
        $this->acknowledged = isset($data['acknowledged']) ? (bool) ($data['acknowledged']) : null;
        $this->success = isset($data['success']) ? (bool) ($data['success']) : null;

        foreach (['error', 'warning', 'notice'] as $logLevel) {
            foreach ($data[$logLevel] ?? [] as $message) {
                $logger->log($logLevel, $message);
            }
        }
    }

    public function getFirstErrorWarning(): ?string
    {
        $errors = $this->data['error'] ?? [];
        $warnings = $this->data['warning'] ?? [];

        return match (true) {
            (\count($errors) > 0) => \array_shift($errors),
            (\count($warnings) > 0) => \array_shift($warnings),
            default => null,
        };
    }

    /**
     * @return array<mixed>
     */
    public function getData(): array
    {
        return $this->data;
    }

    public function isSuccess(): bool
    {
        return $this->success ?? true;
    }

    public function isAcknowledged(): bool
    {
        return $this->acknowledged ?? true;
    }
}
