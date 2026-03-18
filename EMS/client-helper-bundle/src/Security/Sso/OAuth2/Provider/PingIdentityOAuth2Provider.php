<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Security\Sso\OAuth2\Provider;

use League\OAuth2\Client\OptionProvider\HttpBasicAuthOptionProvider;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Token\AccessTokenInterface;
use Symfony\Component\HttpFoundation\Request;

final class PingIdentityOAuth2Provider extends AbstractOAuth2Provider
{
    private readonly GenericProvider $provider;
    private const string DEFAULT_SCOPES = 'openid profile email';

    /**
     * @param string[]|null $scopes
     */
    public function __construct(
        string $issuer,
        string $clientId,
        string $clientSecret,
        private readonly string $redirectUri,
        private readonly ?array $scopes = null,
    ) {
        $this->provider = new GenericProvider(
            options: [
                'clientId' => $clientId,
                'clientSecret' => $clientSecret,
                'redirectUri' => $redirectUri,
                'urlAuthorize' => $issuer.'/authorize',
                'urlAccessToken' => $issuer.'/access_token',
                'urlResourceOwnerDetails' => $issuer.'/userinfo',
            ],
            collaborators: [
                'optionProvider' => new HttpBasicAuthOptionProvider(),
            ]
        );
    }

    protected function getName(): string
    {
        return 'ping';
    }

    protected function getRedirectUri(): string
    {
        return $this->redirectUri;
    }

    protected function getOptions(Request $request): array
    {
        return [
            'redirect_uri' => $this->buildRedirectUri($request),
            'scope' => $this->scopes ? \implode(' ', $this->scopes) : self::DEFAULT_SCOPES,
        ];
    }

    protected function getProvider(): AbstractProvider
    {
        return $this->provider;
    }

    /** @param AccessToken $accessToken */
    public function getUserInfo(AccessTokenInterface $accessToken): array
    {
        $data = $this->provider->getResourceOwner($accessToken)->toArray();

        return [
            'username' => $data['preferred_username'] ?? $data['name'] ?? $data['sub'] ?? null,
            'email' => $data['email'] ?? null,
        ];
    }
}
