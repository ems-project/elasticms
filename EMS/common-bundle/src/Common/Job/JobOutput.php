<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\Job;

use Symfony\Component\Console\Output\Output;

class JobOutput extends Output
{
    private const JOB_VERBOSITY = self::VERBOSITY_NORMAL;

    public function __construct()
    {
        parent::__construct(self::JOB_VERBOSITY);
    }

    public function setVerbosity(int $level): void
    {
        parent::setVerbosity(self::JOB_VERBOSITY);
    }

    public function doWrite(string $message, bool $newline): void
    {
        \dump($message);
    }
}
