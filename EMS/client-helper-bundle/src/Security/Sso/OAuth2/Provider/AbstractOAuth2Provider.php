<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Security\Sso\OAuth2\Provider;

use EMS\ClientHelperBundle\Security\Sso\OAuth2\OAuth2Token;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Token\AccessTokenInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;

abstract class AbstractOAuth2Provider implements ProviderInterface
{
    abstract protected function getName(): string;

    abstract protected function getRedirectUri(): string;

    /**
     * @return array<string, mixed>
     */
    abstract protected function getOptions(Request $request): array;

    abstract protected function getProvider(): AbstractProvider;

    #[\Override]
    public function createToken(AccessTokenInterface $accessToken, Passport $passport, string $firewallName): OAuth2Token
    {
        return new OAuth2Token($accessToken, $passport->getUser(), $firewallName, $passport->getUser()->getRoles());
    }

    #[\Override]
    public function redirect(Request $request): RedirectResponse
    {
        $options = $this->getOptions($request);
        $url = $this->getProvider()->getAuthorizationUrl($options);

        $request->getSession()->set($this->getName(), $this->getProvider()->getState());

        return new RedirectResponse($url);
    }

    #[\Override]
    public function refreshToken(Request $request, OAuth2Token $token): OAuth2Token
    {
        if (!$token->getAccessToken()->hasExpired()) {
            return $token;
        }

        if (null === $user = $token->getUser()) {
            throw new AuthenticationException('User not found');
        }

        $options = $this->getOptions($request);
        $options['refresh_token'] = $token->getAccessToken()->getRefreshToken();
        $refreshedToken = $this->getProvider()->getAccessToken('refresh_token', $options);

        return new OAuth2Token(
            accessToken: $refreshedToken,
            user: $user,
            firewallName: $token->getFirewallName(),
            roles: $token->getRoleNames()
        );
    }

    #[\Override]
    public function getAccessToken(Request $request): AccessTokenInterface
    {
        $expectedState = $request->getSession()->get($this->getName());
        $actualState = $request->query->get('state');

        if (!$actualState || ($actualState !== $expectedState)) {
            throw new AuthenticationException('Invalid state');
        }

        $code = $request->query->get('code');
        if (!$code) {
            throw new AuthenticationException('Code missing');
        }

        $options = $this->getOptions($request);
        $options['code'] = $code;

        return $this->getProvider()->getAccessToken('authorization_code', $options);
    }

    protected function buildRedirectUri(Request $request): string
    {
        $redirectUri = $this->getRedirectUri();

        if (\str_starts_with($redirectUri, '/')) {
            return $request->getSchemeAndHttpHost().$redirectUri;
        }

        return $redirectUri;
    }
}
