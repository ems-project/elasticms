<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Contracts\CoreApi\Endpoint\Admin;

use EMS\CommonBundle\Common\CoreApi\Endpoint\Admin\Message\Job;
use Symfony\Component\Console\Output\OutputInterface;

interface AdminInterface
{
    public function getConfig(string $typeName): ConfigInterface;

    /**
     * @return array{id: string, created: string, modified: string, command: string, user: string, started: bool, done: bool, output: ?string}
     */
    public function getJobStatus(string $jobId): array;

    public function startJob(string $jobId): void;

    /**
     * @return string[]
     */
    public function getConfigTypes(): array;

    /**
     * @return string[]
     */
    public function getContentTypes(): array;

    public function writeJobOutput(string $jobId, OutputInterface $output): void;

    public function runCommand(string $command, OutputInterface $output): void;

    public function getNextJob(string $tag): ?Job;

    public function jobCompleted(Job $job): void;

    public function jobFailed(Job $job, string $getMessage): void;

    public function jobDoWrite(Job $job, string $message, bool $newline): void;

    /**
     * @return string[]
     */
    public function getVersions(): array;
}
