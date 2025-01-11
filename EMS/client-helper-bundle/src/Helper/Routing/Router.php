<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Routing;

use EMS\ClientHelperBundle\Helper\Environment\EnvironmentHelper;
use Symfony\Component\Routing\RouteCollection;

final class Router extends BaseRouter
{
    public function __construct(
        private readonly EnvironmentHelper $environmentHelper,
        private readonly RoutingBuilder $builder,
    ) {
    }

    #[\Override]
    public function getRouteCollection(): RouteCollection
    {
        $environment = $this->environmentHelper->getCurrentEnvironment();

        if (null === $environment || !$environment->isRouterEnabled()) {
            return new RouteCollection();
        }

        return $this->builder->buildRouteCollection($environment);
    }
}
