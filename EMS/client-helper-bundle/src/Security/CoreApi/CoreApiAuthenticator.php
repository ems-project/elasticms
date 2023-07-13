<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Security\CoreApi;

use EMS\ClientHelperBundle\Security\CoreApi\User\CoreApiUserProvider;
use EMS\CommonBundle\Contracts\CoreApi\Exception\NotAuthenticatedExceptionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class CoreApiAuthenticator extends AbstractAuthenticator
{
    use TargetPathTrait;

    public function __construct(
        private readonly HttpUtils $httpUtils,
        private readonly CoreApiFactory $coreApiFactory,
        private readonly CoreApiUserProvider $coreApiUserProvider,
        private readonly string $routeLogin,
    ) {
    }

    public function supports(Request $request): ?bool
    {
        return $request->isMethod(Request::METHOD_POST) && $request->get('_route') === $this->routeLogin;
    }

    public function authenticate(Request $request): Passport
    {
        $attributes = ['_username', '_password', '_csrf_token'];

        list($username, $password, $csrfToken) = \array_map(fn (string $a) => $request->get($a), $attributes);
        $request->getSession()->set(Security::LAST_USERNAME, $username);

        try {
            $coreApi = $this->coreApiFactory->create();
            $coreApi->authenticate($username, $password);
        } catch (NotAuthenticatedExceptionInterface $e) {
            throw new AuthenticationException('emsch.security.login.invalid', 0, $e);
        } catch (\Throwable $e) {
            throw new AuthenticationException('emsch.security.login.exception', 0, $e);
        }

        return new SelfValidatingPassport(
            new UserBadge(
                $coreApi->getToken(),
                fn (string $token) => $this->coreApiUserProvider->loadUserByIdentifier($token)
            ),
            [new CsrfTokenBadge('authenticate', $csrfToken)]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $targetPath = $this->getTargetPath($request->getSession(), $firewallName);

        return $this->httpUtils->createRedirectResponse($request, $targetPath ?? '/');
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $request->getSession()->set(Security::AUTHENTICATION_ERROR, $exception);

        return $this->httpUtils->createRedirectResponse($request, $this->routeLogin);
    }
}
