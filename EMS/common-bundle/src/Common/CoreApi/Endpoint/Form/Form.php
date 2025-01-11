<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\CoreApi\Endpoint\Form;

use EMS\CommonBundle\Common\CoreApi\Client;
use EMS\CommonBundle\Contracts\CoreApi\Endpoint\Form\FormInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class Form implements FormInterface
{
    /** @var string[] */
    private array $endPoint = ['api', 'forms'];

    public function __construct(private readonly Client $client)
    {
    }

    #[\Override]
    public function submit(array $data): array
    {
        $resource = $this->makeResource('submissions');

        return $this->client->post($resource, $data)->getData();
    }

    #[\Override]
    public function getSubmission(string $submissionId, ?string $property = null): array
    {
        $resource = $this->makeResource('submissions/'.$submissionId);
        $query = \array_filter(['property' => $property]);

        return $this->client->get($resource, $query)->getData();
    }

    #[\Override]
    public function getSubmissionFile(string $submissionId, ?string $submissionFileId): ResponseInterface
    {
        $resource = $this->makeResource(\sprintf('submissions/%s/files/%s', $submissionId, $submissionFileId));

        return $this->client->getResponse($resource);
    }

    #[\Override]
    public function getSubmissionFileAsStreamResponse(string $submissionId, ?string $submissionFileId): StreamedResponse
    {
        $resource = $this->makeResource(\sprintf('submissions/%s/files/%s', $submissionId, $submissionFileId));

        return $this->client->forwardResponse($resource);
    }

    #[\Override]
    public function createVerification(string $value): string
    {
        $resource = $this->makeResource('verifications');

        $data = $this->client->post($resource, ['value' => $value])->getData();

        return $data['code'];
    }

    #[\Override]
    public function getVerification(string $value): string
    {
        $resource = $this->makeResource('verifications');

        $data = $this->client->get($resource, ['value' => $value])->getData();

        return $data['code'];
    }

    private function makeResource(?string ...$path): string
    {
        return \implode('/', \array_merge($this->endPoint, \array_filter($path)));
    }
}
