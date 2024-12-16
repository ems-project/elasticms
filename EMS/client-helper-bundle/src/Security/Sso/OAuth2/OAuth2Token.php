<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Security\Sso\OAuth2;

use League\OAuth2\Client\Token\AccessTokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authenticator\Token\PostAuthenticationToken;

class OAuth2Token extends PostAuthenticationToken
{
    /** @var array<string, AccessTokenInterface> */
    public array $serviceTokens = [];

    public function __construct(
        private readonly AccessTokenInterface $accessToken,
        UserInterface $user,
        string $firewallName,
        array $roles,
    ) {
        parent::__construct($user, $firewallName, $roles);
    }

    public function getAccessToken(?string $service = null): AccessTokenInterface
    {
        return $service ? $this->serviceTokens[$service] : $this->accessToken;
    }

    public function getToken(?string $service = null): string
    {
        return $service ? $this->serviceTokens[$service]->getToken() : $this->accessToken->getToken();
    }

    public function __serialize(): array
    {
        return [$this->accessToken, $this->serviceTokens, parent::__serialize()];
    }

    /**
     * @param array<mixed> $data
     */
    public function __unserialize(array $data): void
    {
        [$this->accessToken, $this->serviceTokens, $parentData] = $data;
        parent::__unserialize($parentData);
    }

    public function hasRefreshToken(): bool
    {
        return null !== $this->accessToken->getRefreshToken();
    }

    public function isExpired(): bool
    {
        foreach ($this->serviceTokens as $serviceToken) {
            if ($serviceToken->hasExpired()) {
                return true;
            }
        }

        return $this->accessToken->hasExpired();
    }
}
