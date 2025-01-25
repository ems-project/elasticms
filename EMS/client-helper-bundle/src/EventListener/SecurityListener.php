<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\EventListener;

use EMS\ClientHelperBundle\Security\CoreApi\User\CoreApiUser;
use EMS\ClientHelperBundle\Security\Sso\OAuth2\OAuth2Service;
use EMS\ClientHelperBundle\Security\Sso\OAuth2\OAuth2Token;
use EMS\CommonBundle\Contracts\CoreApi\CoreApiInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

readonly class SecurityListener implements EventSubscriberInterface
{
    public function __construct(
        private AuthorizationCheckerInterface $authorizationChecker,
        private TokenStorageInterface $tokenStorage,
        private OAuth2Service $oAuth2Service,
        private CoreApiInterface $coreApi
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

    public function setToken(): void
    {
        $user = $this->tokenStorage->getToken()?->getUser();
        if ($user instanceof CoreApiUser) {
            $this->coreApi->setToken($user->getToken());
        }
    }

    public function refreshToken(): void
    {
        $token = $this->tokenStorage->getToken();

        if ($token instanceof OAuth2Token && $token->isExpired() && $token->hasRefreshToken()) {
            $this->tokenStorage->setToken($this->oAuth2Service->refreshToken($token));
        }
    }

    public function forceAuthenticated(ControllerEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $forceAuthenticated = $request->attributes->get('_authenticated', false);

        if ($forceAuthenticated && !$this->authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw new AccessDeniedException();
        }
    }
}
