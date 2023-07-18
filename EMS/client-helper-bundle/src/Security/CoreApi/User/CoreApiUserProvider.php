<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Security\CoreApi\User;

use EMS\ClientHelperBundle\Security\CoreApi\CoreApiFactory;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class CoreApiUserProvider implements UserProviderInterface
{
    public function __construct(
        private readonly CoreApiFactory $coreApiFactory,
        private readonly LoggerInterface $logger
    ) {
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof CoreApiUser) {
            throw new UnsupportedUserException();
        }

        return $user;
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

        try {
            $profile = $coreApi->user()->getProfileAuthenticated();

            return new CoreApiUser($profile, $identifier);
        } catch (\Throwable $e) {
            $this->logger->error($e->getMessage(), ['trace' => $e->getTraceAsString(), 'code' => $e->getCode()]);
            throw new CustomUserMessageAuthenticationException('emsch.security.exception.error');
        }
    }
}
