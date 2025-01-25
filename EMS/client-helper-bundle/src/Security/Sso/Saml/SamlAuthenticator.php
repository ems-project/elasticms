<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Security\Sso\Saml;

use EMS\ClientHelperBundle\Security\Sso\User\SsoUser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class SamlAuthenticator extends AbstractAuthenticator
{
    use TargetPathTrait;

    public function __construct(
        private readonly HttpUtils $httpUtils,
        private readonly SamlService $samlService,
    ) {
    }

    #[\Override]
    public function supports(Request $request): ?bool
    {
        return $this->samlService->isEnabled()
            && $request->isMethod(Request::METHOD_POST)
            && $this->httpUtils->checkRequestPath($request, SamlService::ROUTE_ACS);
    }

    #[\Override]
    public function authenticate(Request $request): Passport
    {
        $auth = $this->samlService->auth();
        $auth->processResponse();

        if ($auth->getErrors() && null !== $lastError = $auth->getLastErrorReason()) {
            throw new AuthenticationException($lastError);
        }

        return new SelfValidatingPassport(
            new UserBadge(
                $auth->getNameId(),
                fn (string $userIdentifier) => new SsoUser($userIdentifier)
            )
        );
    }

    #[\Override]
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $loginPath = $this->httpUtils->generateUri($request, SamlService::ROUTE_LOGIN);

        $targetPath = $this->getTargetPath($request->getSession(), $firewallName);
        $path = ($targetPath && $loginPath !== $targetPath ? $targetPath : '/');

        return $this->httpUtils->createRedirectResponse($request, $path);
    }

    #[\Override]
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $request->getSession()->set(SecurityRequestAttributes::AUTHENTICATION_ERROR, $exception);

        return $this->httpUtils->createRedirectResponse($request, SamlService::ROUTE_LOGIN);
    }
}
