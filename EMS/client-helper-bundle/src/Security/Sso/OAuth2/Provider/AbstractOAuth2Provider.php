<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Security\Sso\OAuth2\Provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Token\AccessTokenInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

abstract class AbstractOAuth2Provider implements ProviderInterface
{
    abstract protected function getName(): string;

    /**
     * @return array<string, mixed>
     */
    abstract protected function getOptions(): array;

    abstract protected function getProvider(): AbstractProvider;

    abstract protected function getUsernameFromResource(ResourceOwnerInterface $resourceOwner): ?string;

    /**
     * @param AccessToken $token
     */
    public function getUsername(AccessTokenInterface $token): string
    {
        $resourceOwner = $this->getProvider()->getResourceOwner($token);
        $username = $this->getUsernameFromResource($resourceOwner);

        if (null === $username) {
            throw new AuthenticationException('Could not retrieve username');
        }

        return $username;
    }

    public function redirect(Request $request): RedirectResponse
    {
        $options = $this->getOptions();
        $url = $this->getProvider()->getAuthorizationUrl($options);

        $request->getSession()->set($this->getName(), $this->getProvider()->getState());

        return new RedirectResponse($url);
    }

    public function refreshToken(AccessTokenInterface $token): AccessTokenInterface
    {
        $options = $this->getOptions();
        $options['refresh_token'] = $token->getRefreshToken();

        return $this->getProvider()->getAccessToken('refresh_token', $options);
    }

    public function getAccessToken(Request $request): AccessTokenInterface
    {
        $expectedState = $request->getSession()->get($this->getName());
        $actualState = $request->get('state');

        if (!$actualState || ($actualState !== $expectedState)) {
            throw new AuthenticationException('Invalid state');
        }

        $code = $request->query->get('code');
        if (!$code) {
            throw new AuthenticationException('Code missing');
        }

        $options = $this->getOptions();
        $options['code'] = $code;

        return $this->getProvider()->getAccessToken('authorization_code', $options);
    }
}
