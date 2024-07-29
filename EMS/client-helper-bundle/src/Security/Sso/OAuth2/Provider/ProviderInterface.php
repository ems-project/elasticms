<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Security\Sso\OAuth2\Provider;

use EMS\ClientHelperBundle\Security\Sso\OAuth2\OAuth2Token;
use League\OAuth2\Client\Token\AccessTokenInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;

interface ProviderInterface
{
    public function createToken(AccessTokenInterface $accessToken, Passport $passport, string $firewallName): OAuth2Token;

    public function redirect(Request $request): RedirectResponse;

    public function getAccessToken(Request $request): AccessTokenInterface;

    public function refreshToken(OAuth2Token $token): OAuth2Token;

    public function getUsername(AccessTokenInterface $token): string;
}
