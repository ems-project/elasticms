<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\EventListener;

use EMS\ClientHelperBundle\Security\CoreApi\User\CoreApiUser;
use EMS\ClientHelperBundle\Security\Sso\OAuth2\OAuth2Service;
use EMS\ClientHelperBundle\Security\Sso\OAuth2\OAuth2Token;
use EMS\CommonBundle\Contracts\CoreApi\CoreApiInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

readonly class SecurityListener implements EventSubscriberInterface
{
    public function __construct(
        private AuthorizationCheckerInterface $authorizationChecker,
        private TokenStorageInterface $tokenStorage,
        private OAuth2Service $oAuth2Service,
        private CoreApiInterface $coreApi,
        private string $routeLogin,
        private ?string $firewallRegex,
    ) {
    }

    /**
     * @return array<string, array<mixed>>
     */
    #[\Override]
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [
                ['setToken'],
            ],
            KernelEvents::CONTROLLER => [
                ['refreshToken'],
                ['forceAuthenticated'],
            ],
        ];
    }

    public function setToken(RequestEvent $event): void
    {
        if (!$this->isAuthenticatable($event->getRequest())) {
            return;
        }
        $user = $this->tokenStorage->getToken()?->getUser();
        if ($user instanceof CoreApiUser) {
            $this->coreApi->setToken($user->getToken());
        }
    }

    public function refreshToken(ControllerEvent $event): void
    {
        if (!$this->isAuthenticatable($event->getRequest())) {
            return;
        }
        $token = $this->tokenStorage->getToken();

        if ($token instanceof OAuth2Token && $token->isExpired() && $token->hasRefreshToken()) {
            $this->tokenStorage->setToken($this->oAuth2Service->refreshToken($event->getRequest(), $token));
        }
    }

    public function forceAuthenticated(ControllerEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        if (!$this->firewallMatch($request) && !$request->attributes->get('_authenticated', false)) {
            return;
        }

        if (!$this->authorizationChecker->isGranted(AuthenticatedVoter::IS_AUTHENTICATED_FULLY)) {
            throw new AccessDeniedException();
        }
    }

    private function isAuthenticatable(Request $request): bool
    {
        return $this->firewallMatch($request)
            || $request->attributes->get('_authenticated', false);
    }

    private function firewallMatch(Request $request): bool
    {
        if (0 === \strlen($this->firewallRegex ?? '')) {
            return false;
        }

        if ($request->attributes->get('_route') === $this->routeLogin) {
            return false;
        }

        return (bool) \preg_match('#'.$this->firewallRegex.'#', $request->getPathInfo());
    }
}
