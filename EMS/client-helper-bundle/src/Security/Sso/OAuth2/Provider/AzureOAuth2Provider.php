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

    public function __construct(
        string $tenant,
        string $clientId,
        string $clientSecret,
        string $redirectUri,
        ?string $version = '2.0'
    ) {
        $this->azure = new Azure([
            'tenant' => $tenant,
            'clientId' => $clientId,
            'clientSecret' => $clientSecret,
            'redirectUri' => $redirectUri,
            'defaultEndPointVersion' => $version,
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
