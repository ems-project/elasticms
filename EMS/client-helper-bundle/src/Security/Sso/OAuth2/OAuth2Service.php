<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Security\Sso\OAuth2;

use EMS\ClientHelperBundle\Controller\Security\Sso\OAuth2Controller;
use EMS\ClientHelperBundle\Security\Sso\OAuth2\Provider\AzureOAuth2Provider;
use EMS\ClientHelperBundle\Security\Sso\OAuth2\Provider\KeycloakOAuth2Provider;
use EMS\ClientHelperBundle\Security\Sso\OAuth2\Provider\ProviderInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Loader\Configurator\CollectionConfigurator;
use Symfony\Component\Security\Core\Authentication\Token\NullToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\HttpUtils;

class OAuth2Service
{
    public const ROUTE_LOGIN = 'emsch_oauth2_login';
    public const ROUTE_REDIRECT = 'emsch_oauth2_redirect';

    private ?ProviderInterface $provider = null;

    /**
     * @param array<mixed> $config
     */
    public function __construct(
        private readonly HttpUtils $httpUtils,
        private readonly LoggerInterface $logger,
        private readonly array $config,
    ) {
    }

    public function getProvider(): ProviderInterface
    {
        if (null === $this->provider) {
            $this->provider = $this->createProvider();
        }

        return $this->provider;
    }

    public function isEnabled(): bool
    {
        return $this->config['enabled'] ?? false;
    }

    public function login(Request $request): RedirectResponse
    {
        return $this->httpUtils->createRedirectResponse($request, self::ROUTE_LOGIN);
    }

    public function refreshToken(OAuth2Token $token): TokenInterface
    {
        try {
            return $this->getProvider()->refreshToken($token);
        } catch (\Throwable $e) {
            $this->logger->error($e->getMessage());

            return new NullToken();
        }
    }

    public function registerRoutes(CollectionConfigurator $routes): void
    {
        $redirectUri = $this->property(OAuth2Property::REDIRECT_URI);
        $redirectPath = \parse_url($redirectUri)['path'] ?? null;

        if (null === $redirectPath) {
            throw new \RuntimeException(\sprintf('Could not determine the path from %s', $redirectUri));
        }

        $routes
            ->add(self::ROUTE_LOGIN, '/oauth2/login')
                ->controller([OAuth2Controller::class, 'login'])
                ->methods(['GET'])
            ->add(self::ROUTE_REDIRECT, $redirectPath)
                ->controller([OAuth2Controller::class, 'redirect'])
                ->methods(['GET'])
        ;
    }

    private function createProvider(): ProviderInterface
    {
        return match ($this->property(OAuth2Property::PROVIDER)) {
            'azure' => new AzureOAuth2Provider(
                tenant: $this->property(OAuth2Property::REALM),
                clientId: $this->property(OAuth2Property::CLIENT_ID),
                clientSecret: $this->property(OAuth2Property::CLIENT_SECRET),
                redirectUri: $this->property(OAuth2Property::REDIRECT_URI),
                scopes: $this->config[OAuth2Property::SCOPES->value] ?? null,
                version: $this->optionalProperty(OAuth2Property::VERSION),
            ),
            'keycloak' => new KeycloakOAuth2Provider(
                authServerUrl: $this->property(OAuth2Property::AUTH_SERVER),
                realm: $this->property(OAuth2Property::REALM),
                clientId: $this->property(OAuth2Property::CLIENT_ID),
                clientSecret: $this->property(OAuth2Property::CLIENT_SECRET),
                redirectUri: $this->property(OAuth2Property::REDIRECT_URI),
                version: $this->optionalProperty(OAuth2Property::VERSION),
                encryptionAlgorithm: $this->optionalProperty(OAuth2Property::ENCRYPTION_ALGORITHM),
                encryptionKey: $this->optionalProperty(OAuth2Property::ENCRYPTION_KEY)
            ),
            default => throw new \RuntimeException('invalid provider type'),
        };
    }

    private function property(OAuth2Property $property): string
    {
        return $this->config[$property->value];
    }

    private function optionalProperty(OAuth2Property $property): ?string
    {
        $property = $this->config[$property->value];

        return '' !== $property ? $property : null;
    }
}
