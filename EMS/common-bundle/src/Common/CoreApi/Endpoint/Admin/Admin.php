<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\CoreApi\Endpoint\Admin;

use EMS\CommonBundle\Common\CoreApi\Client;
use EMS\CommonBundle\Contracts\CoreApi\Endpoint\Admin\AdminInterface;
use EMS\CommonBundle\Contracts\CoreApi\Endpoint\Admin\ConfigInterface;
use Symfony\Component\Console\Output\OutputInterface;
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

    public function writeJobOutput(string $jobId, OutputInterface $output): void
    {
        $currentLine = 0;
        while (true) {
            $status = $this->getJobStatus($jobId);
            if (\strlen($status['output'] ?? '') > 0) {
                $counter = 0;
                $lines = \preg_split("/((\r?\n)|(\r\n?))/", $status['output']);
                if (false === $lines) {
                    throw new \RuntimeException('Unexpected false split lines');
                }
                foreach ($lines as $line) {
                    if ($counter++ < $currentLine) {
                        continue;
                    }
                    $currentLine = $counter;
                    $output->writeln(\sprintf("<fg=yellow>></>\t%s", $line));
                }
            }
            if ($status['done']) {
                break;
            }
            \sleep(1);
        }
    }

    public function getConfigTypes(): array
    {
        /** @var string[] $configTypes */
        $configTypes = $this->client->get(\implode('/', ['api', 'admin', 'config-types']))->getData();

        return $configTypes;
    }

    public function getContentTypes(): array
    {
        /** @var string[] $contentTypes */
        $contentTypes = $this->client->get(\implode('/', ['api', 'admin', 'content-types']))->getData();

        return $contentTypes;
    }
}
