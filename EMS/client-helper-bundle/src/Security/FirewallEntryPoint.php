<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Security;

use EMS\ClientHelperBundle\Security\Sso\Saml\SamlConfig;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Component\Security\Http\EntryPoint\Exception\NotAnEntryPointException;
use Symfony\Component\Security\Http\HttpUtils;

class FirewallEntryPoint implements AuthenticationEntryPointInterface
{
    public function __construct(
        private readonly HttpUtils $httpUtils,
        private readonly RouterInterface $router,
        private readonly SamlConfig $samlConfig,
        private readonly string $routeLoginName
    ) {
    }

    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        $routeLogin = $this->router->getRouteCollection()->get($this->routeLoginName);

        return match (true) {
            null !== $routeLogin => $this->httpUtils->createRedirectResponse($request, $this->routeLoginName),
            $this->samlConfig->isEnabled() => $this->httpUtils->createRedirectResponse($request, SamlConfig::ROUTE_LOGIN),
            default => throw new NotAnEntryPointException()
        };
    }
}
