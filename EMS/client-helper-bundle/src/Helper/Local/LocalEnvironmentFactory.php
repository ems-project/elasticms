<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Local;

use EMS\ClientHelperBundle\Helper\Environment\Environment;

final class LocalEnvironmentFactory
{
    public function __construct(
        private readonly string $projectDir,
        private readonly ?string $localPath = null,
    ) {
    }

    public function create(Environment $environment): LocalEnvironment
    {
        $path = $this->localPath ?: 'local'.\DIRECTORY_SEPARATOR.$environment->getAlias();
        $directory = $this->projectDir.\DIRECTORY_SEPARATOR.$path;

        return new LocalEnvironment($environment, $directory);
    }
}
