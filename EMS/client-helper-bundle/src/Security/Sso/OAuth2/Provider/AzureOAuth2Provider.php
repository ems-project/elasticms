<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Security\Sso\OAuth2\Provider;

use EMS\ClientHelperBundle\Security\Sso\OAuth2\OAuth2Token;
use EMS\Helpers\Standard\Type;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessTokenInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use TheNetworg\OAuth2\Client\Provider\Azure;
use TheNetworg\OAuth2\Client\Provider\AzureResourceOwner;

use function Symfony\Component\String\u;

class AzureOAuth2Provider extends AbstractOAuth2Provider
{
    private readonly Azure $azure;
    /** @var array<string, string[]> */
    private array $serviceScopes = [];

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
        ?string $version,
    ) {
        $scopes ??= self::DEFAULT_SCOPES;
        $serviceScopes = \array_filter($scopes, static fn (string $s) => u($s)->startsWith('http'));
        $defaultScopes = \array_diff($scopes, $serviceScopes);

        foreach ($serviceScopes as $serviceScope) {
            $host = \parse_url($serviceScope)['host'] ?? null;
            $serviceName = Type::string($host);
            $this->serviceScopes[$serviceName][] = $serviceScope;
        }

        $this->azure = new Azure([
            'tenant' => $tenant,
            'clientId' => $clientId,
            'clientSecret' => $clientSecret,
            'redirectUri' => $redirectUri,
            'scopes' => $defaultScopes,
            'defaultEndPointVersion' => $version ?? Azure::ENDPOINT_VERSION_2_0,
        ]);
    }

    #[\Override]
    public function createToken(AccessTokenInterface $accessToken, Passport $passport, string $firewallName): OAuth2Token
    {
        $token = new OAuth2Token($accessToken, $passport->getUser(), $firewallName, $passport->getUser()->getRoles());

        foreach ($this->serviceScopes as $serviceName => $serviceScopes) {
            $token->serviceTokens[$serviceName] = $this->azure->getAccessToken('refresh_token', [
                'refresh_token' => $accessToken->getRefreshToken(),
                'scope' => \implode(' ', $serviceScopes),
            ]);
        }

        return $token;
    }

    #[\Override]
    public function refreshToken(OAuth2Token $token): OAuth2Token
    {
        $refreshedToken = parent::refreshToken($token);

        foreach ($token->serviceTokens as $serviceName => $serviceToken) {
            if (!$serviceToken->hasExpired()) {
                continue;
            }

            $refreshedToken->serviceTokens[$serviceName] = $this->azure->getAccessToken('refresh_token', [
                'refresh_token' => $serviceToken->getRefreshToken(),
                'scope' => $this->serviceScopes[$serviceName],
            ]);
        }

        return $refreshedToken;
    }

    #[\Override]
    protected function getName(): string
    {
        return 'azure';
    }

    #[\Override]
    protected function getOptions(): array
    {
        return ['scope' => $this->azure->scope];
    }

    #[\Override]
    protected function getProvider(): AbstractProvider
    {
        return $this->azure;
    }

    /**
     * @param AzureResourceOwner $resourceOwner
     */
    #[\Override]
    protected function getUsernameFromResource(ResourceOwnerInterface $resourceOwner): ?string
    {
        return $resourceOwner->getUpn();
    }
}
