<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\CoreApi\Endpoint\Admin\Message;

use EMS\CommonBundle\Common\CoreApi\Result;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Job
{
    private readonly string $jobId;
    private readonly ?string $output;
    private readonly string $command;

    public function __construct(private readonly Result $result)
    {
        $resolver = new OptionsResolver();
        $resolver->setRequired(['acknowledged', 'success', 'message', 'job_id', 'output', 'command']);
        $resolver->setAllowedTypes('job_id', ['string']);
        $resolver->setAllowedTypes('output', ['string', 'null']);
        $resolver->setAllowedTypes('command', ['string', 'null']);

        /** @var array{job_id: string, output: string|null, command: string|null} */
        $resolved = $resolver->resolve($result->getData());
        $this->jobId = $resolved['job_id'];
        $this->output = $resolved['output'];
        $this->command = $resolved['command'] ?? 'list';
    }

    public function getJobId(): mixed
    {
        return $this->jobId;
    }

    public function getResult(): Result
    {
        return $this->result;
    }

    public function getCommand(): string
    {
        return $this->command ?? 'list';
    }

    public function getOutput(): ?string
    {
        return $this->output;
    }
}
