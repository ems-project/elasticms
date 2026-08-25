<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Security\Sso\OAuth2\Provider;

use EMS\Helpers\Standard\Base64;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Token\AccessTokenInterface;
use Stevenmaguire\OAuth2\Client\Provider\Keycloak;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class KeycloakOAuth2Provider extends AbstractOAuth2Provider
{
    private readonly Keycloak $keycloak;

    public function __construct(
        string $authServerUrl,
        string $realm,
        string $clientId,
        string $clientSecret,
        private readonly string $redirectUri,
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

    #[\Override]
    protected function getName(): string
    {
        return 'keycloak';
    }

    #[\Override]
    protected function getRedirectUri(): string
    {
        return $this->redirectUri;
    }

    protected function getOptions(Request $request): array
    {
        return ['redirect_uri' => $this->buildRedirectUri($request)];
    }

    #[\Override]
    protected function getProvider(): AbstractProvider
    {
        return $this->keycloak;
    }

    public function decodeAccessToken(AccessTokenInterface $accessToken): array
    {
        $token = $this->keycloak->decryptResponse($accessToken->getToken());

        if (!\is_array($token)) {
            throw new AuthenticationException('Invalid token');
        }

        return [
            'username' => $token['preferred_username'] ?? null,
            'email' => $token['email'] ?? null,
            ...$token,
        ];
    }
}
