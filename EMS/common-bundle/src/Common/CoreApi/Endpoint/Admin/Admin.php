<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\CoreApi\Endpoint\Admin;

use EMS\CommonBundle\Common\CoreApi\Client;
use EMS\CommonBundle\Common\CoreApi\Endpoint\Admin\Message\Job;
use EMS\CommonBundle\Contracts\CoreApi\Endpoint\Admin\AdminInterface;
use EMS\CommonBundle\Contracts\CoreApi\Endpoint\Admin\ConfigInterface;
use EMS\CoreBundle\Entity\Job as JobEntity;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpClient\Exception\TransportException;

class Admin implements AdminInterface
{
    public function __construct(private readonly Client $client)
    {
    }

    #[\Override]
    public function getConfig(string $typeName): ConfigInterface
    {
        return new Config($this->client, $typeName);
    }

    /**
     * @return array{id: string, created: string, modified: string, command: string, user: string, started: bool, done: bool, output: ?string}
     */
    #[\Override]
    public function getJobStatus(string $jobId): array
    {
        /** @var array{id: string, created: string, modified: string, command: string, user: string, started: bool, done: bool, output: ?string} $status */
        $status = $this->client->get(\implode('/', ['api', 'admin', 'job-status', $jobId]))->getData();

        return $status;
    }

    #[\Override]
    public function startJob(string $jobId): void
    {
        try {
            $this->client->post(\implode('/', ['api', 'admin', 'start-job', $jobId]), [], [
                'max_duration' => 1,
            ]);
        } catch (TransportException) {
        }
    }

    #[\Override]
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

    #[\Override]
    public function getConfigTypes(): array
    {
        /** @var string[] $configTypes */
        $configTypes = $this->client->get(\implode('/', ['api', 'admin', 'config-types']))->getData();

        return $configTypes;
    }

    #[\Override]
    public function getContentTypes(): array
    {
        /** @var string[] $contentTypes */
        $contentTypes = $this->client->get(\implode('/', ['api', 'admin', 'content-types']))->getData();

        return $contentTypes;
    }

    #[\Override]
    public function runCommand(string $command, ?OutputInterface $output = null): void
    {
        $job = [
            'class' => JobEntity::class,
            'arguments' => [],
            'properties' => [
                'command' => $command,
            ],
        ];
        $jobId = $this->getConfig('job')->create($job);
        $this->startJob($jobId);
        if (null !== $output) {
            $this->writeJobOutput($jobId, $output);
        }
    }

    #[\Override]
    public function getNextJob(string $tag): ?Job
    {
        $result = $this->client->post(\implode('/', ['api', 'admin', 'next-job', $tag]));
        if (null === ($result->getData()['job_id'] ?? null)) {
            return null;
        }

        return new Job($result);
    }

    #[\Override]
    public function jobCompleted(Job $job): void
    {
        $this->client->post(\implode('/', ['api', 'admin', 'job-completed', $job->getJobId()]));
    }

    #[\Override]
    public function jobFailed(Job $job, string $message): void
    {
        $this->client->post(\implode('/', ['api', 'admin', 'job-failed', $job->getJobId()]), [
            'message' => $message,
        ]);
    }

    #[\Override]
    public function jobDoWrite(Job $job, string $message, bool $newline): void
    {
        $this->client->post(\implode('/', ['api', 'admin', 'job-write', $job->getJobId()]), [
            'message' => $message,
            'new-line' => $newline,
        ]);
    }

    #[\Override]
    public function getVersions(): array
    {
        try {
            return $this->client->get(\implode('/', ['api', 'admin', 'versions']))->getData();
        } catch (\Throwable) {
            return [
                'core' => '1.0.0',
                'client' => '1.0.0',
                'common' => '1.0.0',
                'form' => '1.0.0',
                'submission' => '1.0.0',
                'symfony' => '3.0.0',
            ];
        }
    }

    #[\Override]
    public function getCoreVersion(): string
    {
        return $this->getVersions()['core'];
    }
}
