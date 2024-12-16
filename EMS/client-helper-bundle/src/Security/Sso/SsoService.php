<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Security\Sso;

use EMS\ClientHelperBundle\Security\Sso\OAuth2\OAuth2Service;
use EMS\ClientHelperBundle\Security\Sso\Saml\SamlService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Loader\Configurator\CollectionConfigurator;

class SsoService
{
    public function __construct(
        private readonly OAuth2Service $oAuth2Service,
        private readonly SamlService $samlService,
    ) {
    }

    public function enabled(): bool
    {
        return $this->samlService->isEnabled() || $this->oAuth2Service->isEnabled();
    }

    public function start(Request $request): RedirectResponse
    {
        return match (true) {
            $this->oAuth2Service->isEnabled() => $this->oAuth2Service->login($request),
            $this->samlService->isEnabled() => $this->samlService->login($request),
            default => throw new \RuntimeException('Could not start sso, nothing enabled'),
        };
    }

    public function registerRoutes(CollectionConfigurator $routes): void
    {
        if ($this->oAuth2Service->isEnabled()) {
            $this->oAuth2Service->registerRoutes($routes);
        }

        if ($this->samlService->isEnabled()) {
            $this->samlService->registerRoutes($routes);
        }
    }
}
