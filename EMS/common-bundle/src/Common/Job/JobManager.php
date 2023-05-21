<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\Job;

use EMS\CommonBundle\Common\CoreApi\Endpoint\Admin\Message\Job;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\HttpKernel\KernelInterface;

class JobManager
{
    public function __construct(private readonly KernelInterface $kernel)
    {
    }

    public function run(Job $job): void
    {
        $output = new JobOutput();

        try {
            $application = new Application($this->kernel);
            $application->setAutoExit(false);

            $command = ($job->getCommand() ?? 'list');
            $input = new StringInput($command);

            $application->run($input, $output);
        } catch (\Exception $e) {
            $output->writeln(\sprintf('An exception has been raised: %s', $e->getMessage()));
        }
        \dump('Job done');
    }
}
