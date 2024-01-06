<?php

declare(strict_types=1);

namespace EMS\FormBundle\Submission;

use EMS\Helpers\Standard\Json;

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
            return Json::encode($responses);
        } catch (\Throwable) {
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
