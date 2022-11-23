<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Local;

use EMS\ClientHelperBundle\Helper\Environment\Environment;

final class LocalEnvironmentFactory
{
    private string $path;

    public function __construct(string $projectDir)
    {
        $this->path = $projectDir.DIRECTORY_SEPARATOR.'local';
    }

    public function create(Environment $environment): LocalEnvironment
    {
        return new LocalEnvironment($environment, $this->path);
    }
}
