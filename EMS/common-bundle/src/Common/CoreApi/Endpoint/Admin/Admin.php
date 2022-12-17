<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\CoreApi\Endpoint\Admin;

use EMS\CommonBundle\Common\CoreApi\Client;
use EMS\CommonBundle\Contracts\CoreApi\Endpoint\Admin\AdminInterface;
use EMS\CommonBundle\Contracts\CoreApi\Endpoint\Admin\ConfigInterface;
use Symfony\Component\HttpClient\Exception\TransportException;

final class Admin implements AdminInterface
{
    public function __construct(private readonly Client $client)
    {
    }

    public function getConfig(string $typeName): ConfigInterface
    {
        return new Config($this->client, $typeName);
    }

    /**
     * @return array{id: string, created: string, modified: string, command: string, user: string, started: bool, done: bool, output: ?string}
     */
    public function getJobStatus(string $jobId): array
    {
        /** @var array{id: string, created: string, modified: string, command: string, user: string, started: bool, done: bool, output: ?string} $status */
        $status = $this->client->get(\implode('/', ['api', 'admin', 'job-status', $jobId]))->getData();

        return $status;
    }

    public function startJob(string $jobId): void
    {
        try {
            $this->client->post(\implode('/', ['api', 'admin', 'start-job', $jobId]), [], [
                'max_duration' => 1,
            ]);
        } catch (TransportException) {
        }
    }

    public function getConfigTypes(): array
    {
        /** @var string[] $configTypes */
        $configTypes = $this->client->get(\implode('/', ['api', 'admin', 'config-types']))->getData();

        return $configTypes;
    }
}
