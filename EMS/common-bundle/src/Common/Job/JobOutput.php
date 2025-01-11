<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\Job;

use EMS\CommonBundle\Common\CoreApi\Endpoint\Admin\Message\Job;
use EMS\CommonBundle\Contracts\CoreApi\Endpoint\Admin\AdminInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;

class JobOutput extends Output
{
    private const int JOB_VERBOSITY = self::VERBOSITY_NORMAL;

    public function __construct(private readonly AdminInterface $admin, private readonly Job $job, private readonly ?OutputInterface $otherOutput)
    {
        parent::__construct(self::JOB_VERBOSITY);
    }

    #[\Override]
    public function setVerbosity(int $level): void
    {
        parent::setVerbosity(self::JOB_VERBOSITY);
    }

    #[\Override]
    public function doWrite(string $message, bool $newline): void
    {
        $this->admin->jobDoWrite($this->job, $message, $newline);
        if (null !== $this->otherOutput) {
            $this->otherOutput->write($message, $newline);
        }
    }
}
