<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Security\Sso\User;

use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * @implements UserProviderInterface<SsoUser>
 */
class SsoUserProvider implements UserProviderInterface
{
    #[\Override]
    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof SsoUser) {
            throw new UnsupportedUserException();
        }

        return $user;
    }

    #[\Override]
    public function supportsClass(string $class): bool
    {
        return SsoUser::class === $class;
    }

    public function loadUserByUsername(string $username): UserInterface
    {
        return $this->loadUserByIdentifier($username);
    }

    #[\Override]
    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        return new SsoUser($identifier);
    }
}
