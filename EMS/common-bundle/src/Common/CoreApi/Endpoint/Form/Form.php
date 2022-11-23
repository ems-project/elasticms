<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\CoreApi\Endpoint\Form;

use EMS\CommonBundle\Common\CoreApi\Client;
use EMS\CommonBundle\Contracts\CoreApi\Endpoint\Form\FormInterface;

final class Form implements FormInterface
{
    private Client $client;
    /** @var string[] */
    private array $endPoint;

    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->endPoint = ['api', 'forms'];
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
