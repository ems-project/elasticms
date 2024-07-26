<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Security\Sso\OAuth2;

use League\OAuth2\Client\Token\AccessTokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authenticator\Token\PostAuthenticationToken;

class OAuth2Token extends PostAuthenticationToken
{
    public function __construct(
        private readonly AccessTokenInterface $accessToken,
        UserInterface $user,
        string $firewallName,
        array $roles
    ) {
        parent::__construct($user, $firewallName, $roles);
    }

    public static function refresh(AccessTokenInterface $freshAccessToken, OAuth2Token $previous): self
    {
        if (null === $user = $previous->getUser()) {
            throw new AuthenticationException('User not found');
        }

        return new self(
            accessToken: $freshAccessToken,
            user: $user,
            firewallName: $previous->getFirewallName(),
            roles: $previous->getRoleNames()
        );
    }

    public function getAccessToken(): AccessTokenInterface
    {
        return $this->accessToken;
    }

    public function getAccess(): string
    {
        return $this->accessToken->getToken();
    }

    public function __serialize(): array
    {
        return [$this->accessToken, parent::__serialize()];
    }

    /**
     * @param array<mixed> $data
     */
    public function __unserialize(array $data): void
    {
        [$this->accessToken, $parentData] = $data;
        parent::__unserialize($parentData);
    }

    public function hasRefreshToken(): bool
    {
        return null !== $this->accessToken->getRefreshToken();
    }

    public function isExpired(): bool
    {
        return $this->accessToken->hasExpired();
    }
}
