<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Security\Sso;

use EMS\ClientHelperBundle\Security\CoreApi\User\CoreApiUserProvider;
use EMS\ClientHelperBundle\Security\Sso\OAuth2\OAuth2Service;
use EMS\ClientHelperBundle\Security\Sso\Saml\SamlService;
use EMS\ClientHelperBundle\Security\Sso\User\SsoUserProvider;
use EMS\CommonBundle\Contracts\CoreApi\CoreApiInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Loader\Configurator\CollectionConfigurator;
use Symfony\Component\Security\Core\User\UserInterface;

class SsoService
{
    /**
     * @param array<int, array{expression?: string, group: string}> $coreUserGroups
     */
    public function __construct(
        private readonly OAuth2Service $oAuth2Service,
        private readonly SamlService $samlService,
        private readonly SsoUserProvider $ssoUserProvider,
        private readonly CoreApiUserProvider $coreApiUserProvider,
        private readonly CoreApiInterface $coreApi,
        private readonly LoggerInterface $logger,
        private readonly bool $coreUser,
        private readonly array $coreUserGroups
    ) {
    }

    public function enabled(): bool
    {
        return $this->samlService->isEnabled() || $this->oAuth2Service->isEnabled();
    }

    /**
     * @param array<mixed> $token
     */
    public function getUserGroup(array $token): ?string
    {
        if (0 === \count($this->coreUserGroups)) {
            return null;
        }

        try {
            $expressionLanguage = new ExpressionLanguage();

            foreach ($this->coreUserGroups as $entry) {
                if (!isset($entry['expression']) || $expressionLanguage->evaluate($entry['expression'], $token)) {
                    return $entry['group'];
                }
            }

            return null;
        } catch (\Throwable $throwable) {
            $this->logger->error(\sprintf('EMSCH_SSO_CORE_USER_GROUPS failed: %s', $throwable->getMessage()));

            return null;
        }
    }

    public function loadUser(string $userIdentifier, ?string $email = null, ?string $group = null): UserInterface
    {
        $token = $this->authenticateCoreUser($userIdentifier, $email, $group);

        if (null !== $token) {
            return $this->coreApiUserProvider->loadUserByIdentifier($token);
        }

        return $this->ssoUserProvider->loadUserByIdentifierOrEmail($userIdentifier, $email);
    }

    private function authenticateCoreUser(string $userIdentifier, ?string $email, ?string $group): ?string
    {
        if (!$this->coreUser || !$this->coreApi->isAuthenticated()) {
            return null;
        }

        try {
            return $this->coreApi->user()->proxyAuthenticate($userIdentifier, $email, $group);
        } catch (\Throwable $throwable) {
            $this->logger->error(\sprintf('Core proxy authentication failed: %s', $throwable->getMessage()));

            return null;
        }
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
