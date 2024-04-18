<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Security\Sso;

use EMS\ClientHelperBundle\Controller\Security\Sso\SamlController;
use EMS\ClientHelperBundle\Security\Sso\Saml\SamlConfig;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Loader\Configurator\CollectionConfigurator;
use Symfony\Component\Security\Http\HttpUtils;

class SsoService
{
    public function __construct(
        private readonly SamlConfig $samlConfig,
        private readonly HttpUtils $httpUtils,
    ) {
    }

    public function enabled(): bool
    {
        return $this->samlConfig->isEnabled();
    }

    public function start(Request $request): RedirectResponse
    {
        if ($this->samlConfig->isEnabled()) {
            return $this->httpUtils->createRedirectResponse($request, SamlConfig::ROUTE_LOGIN);
        }

        throw new \RuntimeException('Could not start sso, nothing enabled');
    }

    public function registerRoutes(CollectionConfigurator $routes): void
    {
        if ($this->samlConfig->isEnabled()) {
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
}
