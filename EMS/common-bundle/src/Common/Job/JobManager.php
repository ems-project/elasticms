<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\Job;

use EMS\CommonBundle\Common\Admin\AdminHelper;
use EMS\CommonBundle\Common\CoreApi\Endpoint\Admin\Message\Job;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;

use function Symfony\Component\String\u;

class JobManager
{
    public function __construct(private readonly KernelInterface $kernel, private readonly AdminHelper $adminHelper)
    {
    }

    public function run(Job $job, ?OutputInterface $otherOutput): void
    {
        $output = new JobOutput($this->adminHelper->getCoreApi()->admin(), $job, $otherOutput);

        try {
            $application = new Application($this->kernel);
            $application->setAutoExit(false);

            $command = $job->getCommand();
            $escapedCommand = u($command)->replace('\\', '\\\\')->toString();
            $input = new StringInput($escapedCommand);

            $returnCode = $application->run($input, $output);
            if (Command::SUCCESS !== $returnCode) {
                throw new \RuntimeException(\sprintf('Command return: %d', $returnCode));
            }

            $this->adminHelper->getCoreApi()->admin()->jobCompleted($job);
        } catch (\Exception $e) {
            $this->adminHelper->getCoreApi()->admin()->jobFailed($job, $e->getMessage());
        }
    }
}
