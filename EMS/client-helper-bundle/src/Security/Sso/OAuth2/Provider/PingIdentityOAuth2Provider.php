<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Security\Sso\OAuth2\Provider;

use EMS\Helpers\Standard\Base64;
use EMS\Helpers\Standard\Json;
use League\OAuth2\Client\OptionProvider\HttpBasicAuthOptionProvider;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\GenericProvider;
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
        private readonly string $issuer,
        private readonly string $clientId,
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

    #[\Override]
    protected function getName(): string
    {
        return 'ping';
    }

    #[\Override]
    protected function getRedirectUri(): string
    {
        return $this->redirectUri;
    }

    #[\Override]
    protected function getIssuer(): string
    {
        return $this->issuer;
    }

    #[\Override]
    protected function getClientId(): string
    {
        return $this->clientId;
    }

    #[\Override]
    protected function getOptions(Request $request): array
    {
        return [
            'redirect_uri' => $this->buildRedirectUri($request),
            'scope' => $this->scopes ? \implode(' ', $this->scopes) : self::DEFAULT_SCOPES,
        ];
    }

    #[\Override]
    protected function getProvider(): AbstractProvider
    {
        return $this->provider;
    }

    public function decodeAccessToken(AccessTokenInterface $accessToken): array
    {
        $jwt = $accessToken->getValues()['id_token'];
        $token = $this->decodeJwtPayload($jwt);

        return [
            'username' => $token['name'] ?? $token['sub'] ?? null,
            'email' => $token['email'] ?? null,
            ...$token,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeJwtPayload(string $jwt): array
    {
        $payload = \explode('.', $jwt)[1];
        $json = Base64::decode(\strtr($payload, '-_', '+/'));

        return Json::decode($json);
    }
}
