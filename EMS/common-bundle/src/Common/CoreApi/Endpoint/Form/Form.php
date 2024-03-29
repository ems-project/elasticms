<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\CoreApi\Endpoint\Form;

use EMS\CommonBundle\Common\CoreApi\Client;
use EMS\CommonBundle\Contracts\CoreApi\Endpoint\Form\FormInterface;

final class Form implements FormInterface
{
    /** @var string[] */
    private array $endPoint = ['api', 'forms'];

    public function __construct(private readonly Client $client)
    {
    }

    public function submit(array $data): string
    {
        $resource = $this->makeResource('submissions');

        $data = $this->client->post($resource, $data)->getData();

        return $data['submission_id'];
    }

    public function getSubmission(string $submissionId, string $property = null): array
    {
        $resource = $this->makeResource('submissions/'.$submissionId);
        $query = \array_filter(['property' => $property]);

        return $this->client->get($resource, $query)->getData();
    }

    public function createVerification(string $value): string
    {
        $resource = $this->makeResource('verifications');

        $data = $this->client->post($resource, ['value' => $value])->getData();

        return $data['code'];
    }

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
