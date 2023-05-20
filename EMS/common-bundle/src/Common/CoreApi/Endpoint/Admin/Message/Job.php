<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\CoreApi\Endpoint\Admin\Message;

use EMS\CommonBundle\Common\CoreApi\Result;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Job
{
    private string $jobId;

    public function __construct(private readonly Result $result)
    {
        $resolver = new OptionsResolver();
        $resolver->setAllowedTypes('job_id', ['string']);

        /** @var array{job_id: string} */
        $resolved = $resolver->resolve($result->getData());
        $this->jobId = $resolved['job_id'];
    }

    public function getJobId(): mixed
    {
        return $this->jobId;
    }

    public function getResult(): Result
    {
        return $this->result;
    }
}
