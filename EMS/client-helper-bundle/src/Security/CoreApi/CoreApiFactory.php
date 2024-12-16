<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Security\CoreApi;

use EMS\ClientHelperBundle\Helper\Elasticsearch\ClientRequestManager;
use EMS\ClientHelperBundle\Helper\Environment\EnvironmentApi;
use EMS\CommonBundle\Contracts\CoreApi\CoreApiInterface;

class CoreApiFactory
{
    public function __construct(
        private readonly ClientRequestManager $clientRequestManager,
        private readonly EnvironmentApi $environmentApi,
    ) {
    }

    public function create(): CoreApiInterface
    {
        $currentEnvironment = $this->clientRequestManager->getDefault()->getCurrentEnvironment();

        if (null === $currentEnvironment) {
            throw new \RuntimeException('No current environment');
        }

        return $this->environmentApi->api($currentEnvironment);
    }
}
