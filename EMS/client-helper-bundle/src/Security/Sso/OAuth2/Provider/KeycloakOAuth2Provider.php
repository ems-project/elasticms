<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Security\Sso\OAuth2\Provider;

use EMS\Helpers\Standard\Base64;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use Stevenmaguire\OAuth2\Client\Provider\Keycloak;
use Stevenmaguire\OAuth2\Client\Provider\KeycloakResourceOwner;

class KeycloakOAuth2Provider extends AbstractOAuth2Provider
{
    private readonly Keycloak $keycloak;

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

    #[\Override]
    protected function getName(): string
    {
        return 'keycloak';
    }

    #[\Override]
    protected function getOptions(): array
    {
        return [];
    }

    #[\Override]
    protected function getProvider(): AbstractProvider
    {
        return $this->keycloak;
    }

    /**
     * @param KeycloakResourceOwner $resourceOwner
     */
    #[\Override]
    protected function getUsernameFromResource(ResourceOwnerInterface $resourceOwner): ?string
    {
        return $resourceOwner->getUsername();
    }
}
