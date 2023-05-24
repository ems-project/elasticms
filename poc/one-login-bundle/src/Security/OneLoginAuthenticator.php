<?php

declare(strict_types=1);

namespace EMS\OneLoginBundle\Security;

use Hslavich\OneloginSamlBundle\Security\Http\Authenticator\Passport\Badge\SamlAttributesBadge;
use OneLogin\Saml2\Auth;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\PreAuthenticatedUserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Component\Security\Http\HttpUtils;

class OneLoginAuthenticator extends AbstractAuthenticator implements AuthenticationEntryPointInterface
{

    public function __construct(
        private readonly Auth $oneLoginAuth,
        private readonly HttpUtils $httpUtils,
    )
    {

    }

    public function supports(Request $request): ?bool
    {
        return $request->isMethod('POST')
            && $this->httpUtils->checkRequestPath($request, '/saml/acs');
    }

    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        return new RedirectResponse($this->httpUtils->generateUri($request, '/saml/login'));
    }


    public function authenticate(Request $request): Passport
    {
        if ($request->getPathInfo() == '/saml/acs') {
            $this->oneLoginAuth->processResponse();

            return $this->createPassport();
        }
//
//
//
//
//
//
//       $this->oneLoginAuth->login('/', [], false);


        throw new AuthenticationException('no sso');
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return new RedirectResponse('/');
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        throw $exception;


        return null;
    }

    protected function createPassport(): Passport
    {
        $attributes = $this->extractAttributes();
        $username = $this->extractUsername($attributes);

        return new SelfValidatingPassport(new UserBadge($username), [new SamlAttributesBadge($attributes)]);
    }

    protected function extractAttributes(): array
    {

        $attributes = $this->oneLoginAuth->getAttributesWithFriendlyName();
        $attributes['sessionIndex'] = $this->oneLoginAuth->getSessionIndex();

        return $attributes;
    }

    protected function extractUsername(array $attributes): string
    {
        if (isset($this->options['username_attribute'])) {
            if (!\array_key_exists($this->options['username_attribute'], $attributes)) {
                throw new \RuntimeException('Attribute "'.$this->options['username_attribute'].'" not found in SAML data');
            }

            return $attributes[$this->options['username_attribute']][0];
        }

        return $this->oneLoginAuth->getNameId();
    }
}