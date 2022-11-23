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
        $responses = \array_map(function (HandleResponseInterface $response) {
            return $response->getResponse();
        }, $this->responses);

        $json = \json_encode($responses);

        return false !== $json ? $json : '';
    }

    /**
     * @return array<array{status: string, data: string, success: string}>
     */
    public function getSummaries(): array
    {
        return \array_map(function (HandleResponseInterface $response) {
            return $response->getSummary();
        }, $this->responses);
    }
}
