<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Contracts\CoreApi\Endpoint\Admin;

interface AdminInterface
{
    public function getConfig(string $typeName): ConfigInterface;

    /**
     * @return array{id: string, created: string, modified: string, command: string, user: string, started: bool, done: bool, output: ?string}
     */
    public function getJobStatus(string $jobId): array;

    public function startJob(string $jobId): void;
}
