<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Security\Sso\OAuth2\Provider;

use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Token\AccessTokenInterface;
use Stevenmaguire\OAuth2\Client\Provider\Keycloak;

class KeycloakProvider implements ProviderInterface
{
    private Keycloak $keycloak;

    public function __construct(
        string $authServerUrl,
        string $realm,
        string $clientId,
        string $clientSecret,
        string $redirectUri,
        string $version
    ) {
        $this->keycloak = new Keycloak([
            'authServerUrl' => $authServerUrl,
            'realm' => $realm,
            'clientId' => $clientId,
            'clientSecret' => $clientSecret,
            'redirectUri' => $redirectUri,
            'version' => $version,
        ]);
    }

    public function getAuthorizationUrl(): string
    {
        $scopes = ['openid', 'email', 'profile', 'address'];

        return $this->keycloak->getAuthorizationUrl(['scope' => $scopes]);
    }

    public function getAccessToken(string $code): AccessTokenInterface
    {
        return $this->keycloak->getAccessToken(
            grant: 'authorization_code',
            options: ['code' => $code]
        );
    }

    /**
     * @param AccessToken $token
     */
    public function getUsername(AccessTokenInterface $token): string
    {
        $username = $this->keycloak->getResourceOwner($token)->getUsername();

        if (null === $username) {
            throw new \RuntimeException('Could not retrieve username.');
        }

        return $username;
    }
}
