<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Security\Sso;

use EMS\ClientHelperBundle\Security\CoreApi\User\CoreApiUserProvider;
use EMS\ClientHelperBundle\Security\Sso\OAuth2\OAuth2Service;
use EMS\ClientHelperBundle\Security\Sso\Saml\SamlService;
use EMS\ClientHelperBundle\Security\Sso\User\SsoUserProvider;
use EMS\CommonBundle\Contracts\CoreApi\CoreApiInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Loader\Configurator\CollectionConfigurator;
use Symfony\Component\Security\Core\User\UserInterface;

class SsoService
{
    /**
     * @param array<int, array{expression?: string, roles: string[]}> $roleMapping
     */
    public function __construct(
        private readonly OAuth2Service $oAuth2Service,
        private readonly SamlService $samlService,
        private readonly SsoUserProvider $ssoUserProvider,
        private readonly CoreApiUserProvider $coreApiUserProvider,
        private readonly CoreApiInterface $coreApi,
        private readonly bool $loadCoreUser,
        private readonly array $roleMapping
    ) {
    }

    public function enabled(): bool
    {
        return $this->samlService->isEnabled() || $this->oAuth2Service->isEnabled();
    }

    /**
     * @param array<mixed> $token
     *
     * @return string[]
     */
    public function getRoles(array $token): array
    {
        if (0 === \count($this->roleMapping)) {
            return [];
        }

        $roles = [];
        $expressionLanguage = new ExpressionLanguage();

        foreach ($this->roleMapping as $entry) {
            if (!isset($entry['expression']) || $expressionLanguage->evaluate($entry['expression'], $token)) {
                $roles = [...$roles, ...$entry['roles']];
            }
        }

        return \array_values(\array_unique($roles));
    }

    /**
     * @param array<mixed> $roles
     */
    public function loadUser(string $userIdentifier, ?string $email = null, array $roles = []): UserInterface
    {
        $token = $this->authenticateCoreUser($userIdentifier, $email, $roles);

        if (null !== $token) {
            return $this->coreApiUserProvider->loadUserByIdentifier($token);
        }

        return $this->ssoUserProvider->loadUserByIdentifierOrEmail($userIdentifier, $email);
    }

    /**
     * @param array<mixed> $roles
     */
    private function authenticateCoreUser(string $userIdentifier, ?string $email, array $roles): ?string
    {
        if (!$this->loadCoreUser || !$this->coreApi->isAuthenticated()) {
            return null;
        }

        return $this->coreApi->user()->proxyAuthenticate($userIdentifier, $email, $roles);
    }

    public function oauth2(): OAuth2Service
    {
        return $this->oAuth2Service;
    }

    public function saml(): SamlService
    {
        return $this->samlService;
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
