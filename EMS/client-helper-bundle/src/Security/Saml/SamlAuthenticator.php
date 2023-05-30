<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Security\Saml;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class SamlAuthenticator extends AbstractAuthenticator implements AuthenticationEntryPointInterface
{
    use TargetPathTrait;

    public function __construct(
        private readonly HttpUtils $httpUtils,
        private readonly SamlConfig $samlConfig,
        private readonly SamlAuthFactory $samlAuthFactory,
    ) {
    }

    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        return new RedirectResponse($this->httpUtils->generateUri($request, SamlConfig::PATH_SAML_LOGIN));
    }

    public function supports(Request $request): ?bool
    {
        return $this->samlConfig->isEnabled()
            && $request->isMethod(Request::METHOD_POST)
            && $this->httpUtils->checkRequestPath($request, SamlConfig::PATH_SAML_ACS);
    }

    public function authenticate(Request $request): Passport
    {
        $auth = $this->samlAuthFactory->create();
        $auth->processResponse();

        if ($auth->getErrors() && null !== $lastError = $auth->getLastErrorReason()) {
            throw new AuthenticationException($lastError);
        }

        return new SelfValidatingPassport(new UserBadge($auth->getNameId()));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $targetPath = $this->getTargetPath($request->getSession(), $firewallName);
        $path = ($targetPath && SamlConfig::PATH_SAML_LOGIN !== $targetPath ? $targetPath : '/');

        return $this->httpUtils->createRedirectResponse($request, $path);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $request->getSession()->set(Security::AUTHENTICATION_ERROR, $exception);

        return $this->httpUtils->createRedirectResponse($request, SamlConfig::PATH_SAML_LOGIN);
    }
}
