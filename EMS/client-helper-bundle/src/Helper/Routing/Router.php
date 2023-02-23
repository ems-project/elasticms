<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Routing;

use EMS\ClientHelperBundle\Helper\Environment\EnvironmentHelper;
use Symfony\Component\Routing\RouteCollection;

final class Router extends BaseRouter
{
    public function __construct(
        private readonly EnvironmentHelper $environmentHelper,
        private readonly RoutingBuilder $builder
    ) {
    }

    public function getRouteCollection(): RouteCollection
    {
        $currentEnvironment = $this->environmentHelper->getCurrentEnvironment();

        if (null === $currentEnvironment || $currentEnvironment->isElasticms()) {
            return new RouteCollection();
        }

        return $this->builder->buildRouteCollection($currentEnvironment);
    }
}
