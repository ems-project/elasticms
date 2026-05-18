<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Runner\Service;

use EMS\CommonBundle\Common\HttpClientFactory;
use EMS\CommonBundle\Runner\RunnerStatus;
use EMS\Helpers\Standard\Json;
use GuzzleHttp\Client;

class DockerRemote implements RunnerInterface
{
    private readonly Client $httpClient;

    /**
     * @param string[] $env
     * @param mixed[]  $hostConfig
     */
    public function __construct(
        private readonly string $tag,
        private readonly ?string $workerCommand,
        string $baseUrl,
        private readonly string $image,
        private readonly ?string $imageTag = null,
        private readonly array $env = [],
        private readonly array $hostConfig = [],
        ?string $socketPath = null,
    ) {
        $this->httpClient = HttpClientFactory::create(baseUrl: $baseUrl, socketPath: $socketPath);
    }

    public function getTag(): string
    {
        return $this->tag;
    }

    public function start(array $command): string
    {
        $imageTag = $this->imageTag ?? 'latest';
        $this->httpClient->post('images/create', [
            'query' => [
                'fromImage' => $this->image,
                'tag' => $imageTag,
            ],
            'timeout' => 300,
        ]);

        $response = $this->httpClient->post('containers/create', [
            'json' => [
                'Image' => \sprintf('%s:%s', $this->image, $imageTag),
                'Cmd' => $command,
                'Env' => $this->env,
                'Tty' => true,
                'HostConfig' => $this->hostConfig,
            ],
        ]);
        $data = Json::decode($response->getBody()->getContents());
        $id = $data['Id'] ?? null;
        if (null === $id) {
            throw new \RuntimeException('No Docker Remote Id found');
        }
        $this->httpClient->post(\sprintf('/containers/%s/start', $id));

        return $id;
    }

    public function status(string $id): RunnerStatus
    {
        $response = $this->httpClient->get(\sprintf('containers/%s/json', $id));
        $data = Json::decode($response->getBody()->getContents());
        $status = $data['State']['Status'];

        return match ($status) {
            'created' => RunnerStatus::Pending,
            'running' => RunnerStatus::Running,
            'exited' => RunnerStatus::Succeeded,
            'dead' => RunnerStatus::Failed,
            default => RunnerStatus::Unknown,
        };
    }

    public function output(string $id): string
    {
        $response = $this->httpClient->get(\sprintf('containers/%s/logs', $id), [
            'query' => [
                'stdout' => 'true',
                'stderr' => 'false',
                'tail' => 'all',
            ],
        ]);

        return $response->getBody()->getContents();
    }

    public function getWorkerCommand(): ?string
    {
        return $this->workerCommand;
    }
}
