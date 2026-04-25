<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\Job;

use EMS\CommonBundle\Common\Admin\AdminHelper;
use EMS\CommonBundle\Common\CoreApi\Endpoint\Admin\Message\Job;
use EMS\Helpers\Env\RuntimeEnvPlaceholderResolver;
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
            $envVarResolver = new RuntimeEnvPlaceholderResolver();
            $application->setAutoExit(false);

            $command = $job->getCommand();
            $escapedCommand = u($command)->replace('\\', '\\\\')->toString();
            $resolvedCommand = $envVarResolver->resolve($escapedCommand);
            $input = new StringInput($resolvedCommand);

            $returnCode = $application->run($input, $output);
            if (Command::SUCCESS !== $returnCode) {
                throw new \RuntimeException(\sprintf('Command return: %d', $returnCode));
            }

            $this->adminHelper->getCoreApi()->admin()->jobCompleted($job);
        } catch (\Exception $exception) {
            $this->adminHelper->getCoreApi()->admin()->jobFailed($job, $exception->getMessage());
        }
    }
}
