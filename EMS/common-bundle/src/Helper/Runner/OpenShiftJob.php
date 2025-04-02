<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Helper\Runner;

use EMS\CommonBundle\Common\HttpClientFactory;
use EMS\Helpers\Standard\Json;
use GuzzleHttp\Client;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Yaml\Yaml;

class OpenShiftJob implements RunnerInterface
{
    private Client $httpClient;
    private UuidInterface $uuid;

    public function __construct(
        string $baseUrl,
        string $authKey,
    ) {
        $this->httpClient = HttpClientFactory::create($baseUrl, [
            'Authorization' => \sprintf('Bearer %s', $authKey),
            'Content-Type' => 'application/yaml',
        ]);
        $this->uuid = Uuid::uuid4();
    }

    /**
     * @param string[] $command
     */
    public function start(array $command): string
    {
        $yamlContent = Yaml::dump([
            'apiVersion' => 'batch/v1',
            'kind' => 'Job',
            'metadata' => [
                'name' => $this->uuid->toString(),
            ],
            'spec' => [
                'ttlSecondsAfterFinished' => 3600,
                'template' => [
                    'spec' => [
                        'containers' => [[
                            'name' => 'ems-runner-container',
                            'image' => 'busybox',
                            'command' => $command,
                        ]],
                        'restartPolicy' => 'Never',
                    ],
                ],
            ],
        ], 6);

        $response = $this->httpClient->post('jobs', [
            'body' => $yamlContent,
        ]);
        $responseBody = Json::decode($response->getBody()->getContents());
        \dump($responseBody);

        return 'toto';
    }
}
