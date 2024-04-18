<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Security\Sso;

use EMS\ClientHelperBundle\Security\Sso\Saml\SamlService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Loader\Configurator\CollectionConfigurator;
use Symfony\Component\Security\Http\HttpUtils;

class SsoService
{
    public function __construct(
        private readonly SamlService $samlService,
        private readonly HttpUtils $httpUtils,
    ) {
    }

    public function enabled(): bool
    {
        return $this->samlService->isEnabled();
    }

    public function start(Request $request): RedirectResponse
    {
        if ($this->samlService->isEnabled()) {
            return $this->httpUtils->createRedirectResponse($request, SamlService::ROUTE_LOGIN);
        }

        throw new \RuntimeException('Could not start sso, nothing enabled');
    }

    public function registerRoutes(CollectionConfigurator $routes): void
    {
        if ($this->samlService->isEnabled()) {
            $this->samlService->registerRoutes($routes);
        }
    }
}
