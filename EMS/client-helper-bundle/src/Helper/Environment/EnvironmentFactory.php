<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Environment;

use EMS\ClientHelperBundle\Helper\Local\LocalEnvironmentFactory;

final class EnvironmentFactory
{
    private ?LocalEnvironmentFactory $localEnvironmentFactory = null;

    /**
     * @param array<mixed> $config
     */
    public function create(string $name, array $config): Environment
    {
        $environment = new Environment($name, $config);

        if (null !== $this->localEnvironmentFactory) {
            $environment->setLocal($this->localEnvironmentFactory->create($environment));
        }

        return $environment;
    }

    public function setLocalEnvironmentFactory(?LocalEnvironmentFactory $localEnvironmentFactory): void
    {
        $this->localEnvironmentFactory = $localEnvironmentFactory;
    }
}
