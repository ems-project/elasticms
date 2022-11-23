<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Contracts\Environment;

interface EnvironmentHelperInterface
{
    /**
     * Core channels will add environments.
     *
     * @param array<mixed> $config
     */
    public function addEnvironment(string $name, array $config): void;
}
