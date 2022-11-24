<?php

declare(strict_types=1);

namespace EMS\FormBundle\Submission;

class HandleResponseCollector
{
    /** @var HandleResponseInterface[] */
    private array $responses = [];

    public function addResponse(HandleResponseInterface $response): void
    {
        $this->responses[] = $response;
    }

    /** @return HandleResponseInterface[] */
    public function getResponses(): array
    {
        return $this->responses;
    }

    public function toJson(): string
    {
        $responses = \array_map(fn (HandleResponseInterface $response) => $response->getResponse(), $this->responses);

        try {
            return \json_encode($responses, JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            return '';
        }
    }

    /**
     * @return array<array{status: string, data: string, success: string}>
     */
    public function getSummaries(): array
    {
        return \array_map(fn (HandleResponseInterface $response) => $response->getSummary(), $this->responses);
    }
}
