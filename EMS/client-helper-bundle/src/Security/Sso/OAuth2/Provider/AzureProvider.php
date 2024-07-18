<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Security\Sso\OAuth2\Provider;

use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Token\AccessTokenInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use TheNetworg\OAuth2\Client\Provider\Azure;

class AzureProvider implements ProviderInterface
{
    private Azure $azure;
    private const SESSION_STATE = 'azure-state';

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

    public function redirect(Request $request): RedirectResponse
    {
        $url = $this->azure->getAuthorizationUrl(['scope' => $this->azure->scope]);

        $request->getSession()->set(self::SESSION_STATE, $this->azure->getState());

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

        return $this->azure->getAccessToken('authorization_code', [
           'scope' => $this->azure->scope,
           'code' => $code,
        ]);
    }

    /**
     * @param AccessToken $token
     */
    public function getUsername(AccessTokenInterface $token): string
    {
        $resourceOwner = $this->azure->getResourceOwner($token);
        $username = $resourceOwner->toArray()['upn'] ?? null;

        if (null === $username) {
            throw new \RuntimeException('Could not retrieve username.');
        }

        return $username;
    }
}
