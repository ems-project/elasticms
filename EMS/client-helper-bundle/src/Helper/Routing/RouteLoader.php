<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Routing;

use EMS\ClientHelperBundle\Controller\Security\SamlController;
use EMS\ClientHelperBundle\Security\Saml\SamlConfig;
use Symfony\Component\Routing\Loader\Configurator\CollectionConfigurator;
use Symfony\Component\Routing\RouteCollection;

class RouteLoader
{
    private const PREFIX = 'emsch_';

    public function __construct(private readonly SamlConfig $samlConfig)
    {
    }

    public function __invoke(): RouteCollection
    {
        $routeCollection = new RouteCollection();
        $routes = new CollectionConfigurator($routeCollection, self::PREFIX);
        $routes->add('logout', '/logout')->methods(['GET']);

        if ($this->samlConfig->isEnabled()) {
            $this->addSamlRoutes($routes);
        }

        return $routeCollection;
    }

    private function addSamlRoutes(CollectionConfigurator $routes): void
    {
        $routes
            ->add('saml_metadata', SamlConfig::PATH_SAML_METADATA)
                ->controller([SamlController::class, 'metadata'])
                ->methods(['GET'])
            ->add('saml_login', SamlConfig::PATH_SAML_LOGIN)
                ->controller([SamlController::class, 'login'])
                ->methods(['GET'])
            ->add('saml_acs', SamlConfig::PATH_SAML_ACS)
                ->controller([SamlController::class, 'acs'])
                ->methods(['POST'])
        ;
    }
}
