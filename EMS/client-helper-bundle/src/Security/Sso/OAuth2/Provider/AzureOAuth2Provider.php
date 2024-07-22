<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Security\Sso\OAuth2\Provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use TheNetworg\OAuth2\Client\Provider\Azure;
use TheNetworg\OAuth2\Client\Provider\AzureResourceOwner;

class AzureOAuth2Provider extends AbstractOAuth2Provider
{
    private Azure $azure;

    public const DEFAULT_SCOPES = ['openid', 'profile', 'offline_access'];

    /**
     * @param string[] $scopes
     */
    public function __construct(
        string $tenant,
        string $clientId,
        string $clientSecret,
        string $redirectUri,
        ?array $scopes,
        ?string $version
    ) {
        $this->azure = new Azure([
            'tenant' => $tenant,
            'clientId' => $clientId,
            'clientSecret' => $clientSecret,
            'redirectUri' => $redirectUri,
            'scopes' => $scopes ?? self::DEFAULT_SCOPES,
            'defaultEndPointVersion' => $version ?? Azure::ENDPOINT_VERSION_2_0,
        ]);
    }

    protected function getName(): string
    {
        return 'azure';
    }

    protected function getOptions(): array
    {
        return ['scope' => $this->azure->scope];
    }

    protected function getProvider(): AbstractProvider
    {
        return $this->azure;
    }

    /**
     * @param AzureResourceOwner $resourceOwner
     */
    protected function getUsernameFromResource(ResourceOwnerInterface $resourceOwner): ?string
    {
        return $resourceOwner->getUpn();
    }
}
