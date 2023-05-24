<?php

declare(strict_types=1);

namespace EMS\OneLoginBundle\Security;

use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class OneLoginUserProvider implements UserProviderInterface
{
    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof OneLoginUser) {
            throw new UnsupportedUserException();
        }

        return $user;
    }

    public function supportsClass(string $class): bool
    {
        return $class === OneLoginUser::class;
    }

    public function loadUserByUsername(string $username): UserInterface
    {
        return new OneLoginUser($username);
    }
}