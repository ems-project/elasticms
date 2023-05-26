<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Security\Saml\User;

use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class SamlUserProvider implements UserProviderInterface
{
    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof SamlUser) {
            throw new UnsupportedUserException();
        }

        return $user;
    }

    public function supportsClass(string $class): bool
    {
        return SamlUser::class === $class;
    }

    public function loadUserByUsername(string $username): UserInterface
    {
        return new SamlUser($username);
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        return new SamlUser($identifier);
    }
}
