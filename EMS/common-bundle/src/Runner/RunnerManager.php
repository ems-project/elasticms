<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Runner;

use EMS\CommonBundle\Exception\RunnerNotFoundException;
use EMS\CommonBundle\Runner\Factory\RunnerFactoryInterface;
use EMS\CommonBundle\Runner\Service\RunnerInterface;
use EMS\CoreBundle\Entity\Job;
use EMS\Helpers\Standard\Text;
use Psr\Log\LoggerInterface;

class RunnerManager
{
    /** @var RunnerFactoryInterface[] */
    private array $factories = [];

    /**
     * @param iterable<RunnerFactoryInterface> $factories
     * @param array<array{type?: string}>      $runnerConfigs
     */
    public function __construct(private readonly LoggerInterface $logger, iterable $factories, private readonly array $runnerConfigs = [])
    {
        foreach ($factories as $factory) {
            if (!$factory instanceof RunnerFactoryInterface) {
                throw new \RuntimeException('Unexpected RunnerFactoryInterface class');
            }
            $this->addRunnerFactory($factory);
        }
    }

    private function addRunnerFactory(RunnerFactoryInterface $factory): void
    {
        $this->factories[$factory->getRunnerType()] = $factory;
    }

    private function getRunnerFromConfigs(string $tag): RunnerInterface
    {
        foreach ($this->runnerConfigs as $runnerConfig) {
            if ($tag !== ($runnerConfig[RunnerFactoryInterface::RUNNER_CONFIG_TAG] ?? null)) {
                continue;
            }
            $type = $runnerConfig[RunnerFactoryInterface::RUNNER_CONFIG_TYPE] ?? null;
            if (null === $type) {
                $this->logger->error('Runner type not defined.');
                continue;
            }
            $factory = $this->factories[$type] ?? null;
            if (null === $factory) {
                $this->logger->error(\sprintf('Runner factory "%s" was not found.', $factory));
                continue;
            }

            return $factory->createRunner($runnerConfig);
        }
        throw new RunnerNotFoundException($tag);
    }

    /**
     * @param string[] $command
     */
    public function start(string $tag, array $command): string
    {
        $runner = $this->getRunnerFromConfigs($tag);

        return $runner->start($command);
    }

    public function status(string $tag, string $id): RunnerStatus
    {
        $runner = $this->getRunnerFromConfigs($tag);

        return $runner->status($id);
    }

    public function output(string $tag, string $id): string
    {
        $runner = $this->getRunnerFromConfigs($tag);

        return $runner->output($id);
    }

    public function startJob(Job $job): string
    {
        $runner = $this->getRunnerFromConfigs($job->getTag() ?? '');
        $command = $runner->getWorkerCommand();
        if (null === $command) {
            $command = $job->getCommand() ?? '';
        } else {
            $command = \str_replace(RunnerFactoryInterface::RUNNER_EMS_JOB_ID_REPLACER, (string) $job->getId(), $command);
        }
        $command = Text::shellWords($command);

        return $runner->start($command);
    }
}
