<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Security\Sso\OAuth2;

use EMS\ClientHelperBundle\Security\Sso\User\SsoUser;
use League\OAuth2\Client\Token\AccessTokenInterface;
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

class OAuth2Authenticator extends AbstractAuthenticator
{
    use TargetPathTrait;

    public function __construct(
        private readonly HttpUtils $httpUtils,
        private readonly OAuth2Service $oAuth2Service,
    ) {
    }

    #[\Override]
    public function supports(Request $request): ?bool
    {
        return $this->oAuth2Service->isEnabled()
            && $request->isMethod(Request::METHOD_GET)
            && $this->httpUtils->checkRequestPath($request, OAuth2Service::ROUTE_REDIRECT);
    }

    #[\Override]
    public function authenticate(Request $request): Passport
    {
        $accessToken = $this->oAuth2Service->getProvider()->getAccessToken($request);
        $username = $this->oAuth2Service->getProvider()->getUsername($accessToken);

        $passport = new SelfValidatingPassport(
            userBadge: new UserBadge($username, fn (string $userIdentifier) => new SsoUser(
                identifier: $userIdentifier
            ))
        );
        $passport->setAttribute('access_token', $accessToken);

        return $passport;
    }

    #[\Override]
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $loginPath = $this->httpUtils->generateUri($request, OAuth2Service::ROUTE_LOGIN);

        $targetPath = $this->getTargetPath($request->getSession(), $firewallName);
        $path = ($targetPath && $loginPath !== $targetPath ? $targetPath : '/');

        return $this->httpUtils->createRedirectResponse($request, $path);
    }

    #[\Override]
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $request->getSession()->set(SecurityRequestAttributes::AUTHENTICATION_ERROR, $exception);

        return $this->httpUtils->createRedirectResponse($request, OAuth2Service::ROUTE_LOGIN);
    }

    #[\Override]
    public function createToken(Passport $passport, string $firewallName): TokenInterface
    {
        /** @var AccessTokenInterface $accessToken */
        $accessToken = $passport->getAttribute('access_token');

        return $this->oAuth2Service->getProvider()->createToken($accessToken, $passport, $firewallName);
    }
}
