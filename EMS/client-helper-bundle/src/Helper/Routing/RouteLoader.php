<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Routing;

use EMS\ClientHelperBundle\Controller\Security\SamlController;
use EMS\ClientHelperBundle\Security\Saml\SamlConfig;
use Symfony\Component\Routing\Loader\Configurator\CollectionConfigurator;
use Symfony\Component\Routing\RouteCollection;

class RouteLoader
{
    public function __construct(private readonly SamlConfig $samlConfig)
    {
    }

    public function __invoke(): RouteCollection
    {
        $routeCollection = new RouteCollection();
        $routes = new CollectionConfigurator($routeCollection, '');
        $routes->add('emsch_logout', '/logout')->methods(['GET']);

        if ($this->samlConfig->isEnabled()) {
            $this->addSamlRoutes($routes);
        }

        return $routeCollection;
    }

    private function addSamlRoutes(CollectionConfigurator $routes): void
    {
        $routes
            ->add(SamlConfig::ROUTE_METADATA, '/saml/metadata')
                ->controller([SamlController::class, 'metadata'])
                ->methods(['GET'])
            ->add(SamlConfig::ROUTE_LOGIN, '/saml/login')
                ->controller([SamlController::class, 'login'])
                ->methods(['GET'])
            ->add(SamlConfig::ROUTE_ACS, '/saml/acs')
                ->controller([SamlController::class, 'acs'])
                ->methods(['POST'])
        ;
    }
}
