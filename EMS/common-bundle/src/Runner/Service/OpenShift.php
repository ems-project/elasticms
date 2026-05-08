<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Runner\Service;

use EMS\CommonBundle\Common\HttpClientFactory;
use EMS\CommonBundle\Runner\RunnerStatus;
use EMS\Helpers\Standard\Json;
use GuzzleHttp\Client;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Yaml\Yaml;

class OpenShift implements RunnerInterface
{
    private readonly Client $httpClient;
    private readonly UuidInterface $uuid;

    /**
     * @param array<string, string>                     $labels
     * @param string|bool                               $verifySsl
     * @param array<array{name: string, value: string}> $env
     */
    public function __construct(
        private readonly string $tag,
        private readonly ?string $workerCommand,
        string $baseUrl,
        $verifySsl,
        string $authKey,
        private readonly string $namespace,
        private readonly string $image,
        private readonly ?string $imageTag = null,
        private readonly int $ttlSecondsAfterFinished = 3600,
        private readonly int $backoffLimit = 0,
        private readonly int $activeDeadlineSeconds = 60,
        private readonly array $labels = [],
        private readonly array $env = [],
    ) {
        $this->httpClient = HttpClientFactory::create(baseUrl: $baseUrl, headers: [
            'Authorization' => \sprintf('Bearer %s', $authKey),
            'Content-Type' => 'application/yaml',
        ], verifySsl: $verifySsl);
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
                'labels' => $this->labels,
            ],
            'spec' => [
                'ttlSecondsAfterFinished' => $this->ttlSecondsAfterFinished,
                'backoffLimit' => $this->backoffLimit,
                'activeDeadlineSeconds' => $this->activeDeadlineSeconds,
                'template' => [
                    'metadata' => [
                        'labels' => $this->labels,
                    ],
                    'spec' => [
                        'containers' => [[
                            'name' => 'ems-runner-container',
                            'image' => null !== $this->imageTag ? \sprintf('%s:%s', $this->image, $this->imageTag) : $this->image,
                            'command' => $command,
                            'env' => $this->env,
                        ]],
                        'restartPolicy' => 'Never',
                    ],
                ],
            ],
        ], 6);

        $response = $this->httpClient->post(\sprintf('apis/batch/v1/namespaces/%s/jobs', $this->namespace), [
            'body' => $yamlContent,
        ]);
        if (!\in_array($response->getStatusCode(), [200, 201], true)) {
            throw new \RuntimeException(\sprintf('Response status code: %d', $response->getStatusCode()));
        }

        return $this->uuid->toString();
    }

    public function getTag(): string
    {
        return $this->tag;
    }

    public function status(string $id): RunnerStatus
    {
        $response = $this->httpClient->get(\sprintf('apis/batch/v1/namespaces/%s/jobs/%s', $this->namespace, $id));
        $data = Json::decode($response->getBody()->getContents());
        $conditions = $data['status']['conditions'] ?? [];
        $active = $data['status']['active'] ?? 0;
        $status = RunnerStatus::Unknown;

        foreach ($conditions as $condition) {
            if ('Complete' === $condition['type'] && 'True' === $condition['status']) {
                $status = RunnerStatus::Succeeded;
                break;
            }
            if ('Failed' === $condition['type'] && 'True' === $condition['status']) {
                $status = RunnerStatus::Failed;
                break;
            }
        }
        if (RunnerStatus::Unknown === $status && $active > 0) {
            $status = RunnerStatus::Running;
        }

        return $status;
    }

    public function output(string $id): string
    {
        $response = $this->httpClient->get(\sprintf('/api/v1/namespaces/%s/pods', $this->namespace), [
            'query' => [
                'selector' => 'job-name='.$id,
            ],
        ]);
        $podsData = Json::decode($response->getBody()->getContents());
        $pods = $podsData['items'] ?? [];

        if (empty($pods)) {
            throw new \RuntimeException('No pods available');
        }
        $podName = $pods[0]['metadata']['name'];
        $logResponse = $this->httpClient->get(\sprintf('/api/v1/namespaces/%s/pods/%s/log', $this->namespace, $podName));

        return $logResponse->getBody()->getContents();
    }

    public function getWorkerCommand(): ?string
    {
        return $this->workerCommand;
    }
}
