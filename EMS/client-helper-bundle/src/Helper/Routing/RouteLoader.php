<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Routing;

use EMS\ClientHelperBundle\Security\Sso\SsoService;
use Symfony\Component\Routing\Loader\Configurator\CollectionConfigurator;
use Symfony\Component\Routing\RouteCollection;

class RouteLoader
{
    public function __construct(private readonly SsoService $ssoService)
    {
    }

    public function __invoke(): RouteCollection
    {
        $routeCollection = new RouteCollection();
        $routes = new CollectionConfigurator($routeCollection, '');
        $routes->add('emsch_logout', '/logout')->methods(['GET']);

        if ($this->ssoService->enabled()) {
            $this->ssoService->registerRoutes($routes);
        }

        return $routeCollection;
    }
}
