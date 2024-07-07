<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Security\Sso\OAuth2\Provider;

use EMS\CommonBundle\Common\Standard\Base64;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Token\AccessTokenInterface;
use Stevenmaguire\OAuth2\Client\Provider\Keycloak;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class KeycloakProvider implements ProviderInterface
{
    private Keycloak $keycloak;
    private const SESSION_STATE = 'keycloak-state';

    public function __construct(
        string $authServerUrl,
        string $realm,
        string $clientId,
        string $clientSecret,
        string $redirectUri,
        ?string $version,
        ?string $encryptionAlgorithm,
        ?string $encryptionKey,
    ) {
        $this->keycloak = new Keycloak([
            'authServerUrl' => $authServerUrl,
            'realm' => $realm,
            'clientId' => $clientId,
            'clientSecret' => $clientSecret,
            'redirectUri' => $redirectUri,
            'encryptionAlgorithm' => $encryptionAlgorithm,
        ]);

        if ($version) {
            $this->keycloak->setVersion($version);
        }

        if ($encryptionAlgorithm && $encryptionKey) {
            $this->keycloak->setEncryptionAlgorithm($encryptionAlgorithm);
            $this->keycloak->setEncryptionKey(Base64::decode($encryptionKey));
        }
    }

    public function redirect(Request $request): RedirectResponse
    {
        $url = $this->keycloak->getAuthorizationUrl(['scope' => ['openid', 'email']]);
        $state = $this->keycloak->getState();

        $request->getSession()->set(self::SESSION_STATE, $state);

        return new RedirectResponse($url);
    }

    public function getAccessToken(Request $request): AccessTokenInterface
    {
        $expectedState = $request->getSession()->get(self::SESSION_STATE);
        $actualState = $request->get('state');

        if (!$actualState || ($actualState !== $expectedState)) {
            throw new AuthenticationException('Invalid state');
        }

        $code = $request->query->get('code');
        if (!$code) {
            throw new AuthenticationException('Code missing');
        }

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
