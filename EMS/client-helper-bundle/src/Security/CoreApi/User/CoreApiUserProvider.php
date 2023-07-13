<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Security\CoreApi\User;

use EMS\ClientHelperBundle\Security\CoreApi\CoreApiFactory;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class CoreApiUserProvider implements UserProviderInterface
{
    public function __construct(private readonly CoreApiFactory $coreApiFactory)
    {
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof CoreApiUser) {
            throw new UnsupportedUserException();
        }

        return $this->loadUserByIdentifier($user->getToken());
    }

    public function supportsClass(string $class): bool
    {
        return CoreApiUser::class === $class;
    }

    public function loadUserByUsername(string $username): UserInterface
    {
        return $this->loadUserByIdentifier($username);
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $coreApi = $this->coreApiFactory->create();
        $coreApi->setToken($identifier);
        $profile = $coreApi->user()->getProfileAuthenticated();

        return new CoreApiUser($profile, $identifier);
    }
}
