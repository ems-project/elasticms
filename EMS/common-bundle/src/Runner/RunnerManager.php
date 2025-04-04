<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Runner;

use EMS\CommonBundle\Exception\RunnerNotFoundException;
use EMS\CommonBundle\Runner\Factory\RunnerFactoryInterface;
use EMS\CommonBundle\Runner\Service\RunnerInterface;
use Psr\Log\LoggerInterface;

class RunnerManager
{
    /** @var RunnerInterface[] */
    private array $runners = [];
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
        $this->registerRunnersFromConfigs();
    }

    private function addRunnerFactory(RunnerFactoryInterface $factory): void
    {
        $this->factories[$factory->getRunnerType()] = $factory;
    }

    private function registerRunnersFromConfigs(): void
    {
        foreach ($this->runnerConfigs as $runnerConfig) {
            $type = $runnerConfig['type'] ?? null;
            if (null === $type) {
                $this->logger->error('Runner type not defined.');
                continue;
            }
            $factory = $this->factories[$type] ?? null;
            if (null === $factory) {
                $this->logger->error(\sprintf('Runner factory "%s" was not found.', $factory));
                continue;
            }
            $runner = $factory->createService($runnerConfig);
            if (null !== $runner) {
                $this->addRunner($runner);
            }
        }
    }

    private function addRunner(RunnerInterface $runner): void
    {
        $this->runners[$runner->getTag()] = $runner;
    }

    /**
     * @param string[] $command
     */
    public function start(string $tag, array $command): string
    {
        $runner = $this->runners[$tag] ?? null;
        if (null === $runner) {
            throw new RunnerNotFoundException($tag);
        }

        return $runner->start($command);
    }
}
