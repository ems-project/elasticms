<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Environment;

use EMS\ClientHelperBundle\Contracts\Environment\EnvironmentHelperInterface;
use Symfony\Component\HttpFoundation\RequestStack;

final class EnvironmentHelper implements EnvironmentHelperInterface
{
    /** @var Environment[] */
    private array $environments = [];
    private ?Environment $defaultEnvironment = null;

    /**
     * @param array<string, array<mixed>> $environments
     */
    public function __construct(
        private readonly EnvironmentFactory $environmentFactory,
        private readonly RequestStack $requestStack,
        private readonly string $emschEnv,
        array $environments,
    ) {
        foreach ($environments as $name => $config) {
            $this->addEnvironment($name, $config);
        }
    }

    public function addEnvironment(string $name, array $config): void
    {
        if ($this->emschEnv && !isset($config[Environment::DEFAULT])) {
            $config[Environment::DEFAULT] = ($name === $this->emschEnv);
        }

        $environment = $this->environmentFactory->create($name, $config);
        $this->environments[$name] = $environment;

        if ($environment->isDefault()) {
            $this->defaultEnvironment = $environment;
        }
    }

    public function getEnvironmentDefault(): ?Environment
    {
        return $this->defaultEnvironment;
    }

    public function getEnvironment(string $name): ?Environment
    {
        return $this->environments[$name] ?? null;
    }

    public function giveEnvironment(string $name): Environment
    {
        if (!isset($this->environments[$name])) {
            throw new \RuntimeException(\sprintf('Environment %s not found', $name));
        }

        return $this->environments[$name];
    }

    /**
     * @return Environment[]
     */
    public function getEnvironments(): array
    {
        return $this->environments;
    }

    public function getBackend(): ?string
    {
        $current = $this->requestStack->getCurrentRequest();

        return null !== $current ? $current->get(Environment::BACKEND_ATTRIBUTE) : null;
    }

    public function getLocale(): string
    {
        $current = $this->requestStack->getCurrentRequest();
        if (null === $current) {
            throw new \RuntimeException('Unexpected null request');
        }

        return $current->getLocale();
    }

    public function getCurrentEnvironment(): ?Environment
    {
        foreach ($this->environments as $environment) {
            if ($environment->isActive()) {
                return $environment;
            }
        }

        return $this->defaultEnvironment;
    }
}
