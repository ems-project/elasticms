<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Security\CoreApi\User;

use EMS\CommonBundle\Contracts\CoreApi\CoreApiInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * @implements UserProviderInterface<CoreApiUser>
 */
readonly class CoreApiUserProvider implements UserProviderInterface
{
    public function __construct(
        private CoreApiInterface $coreApi,
        private LoggerInterface $logger,
    ) {
    }

    #[\Override]
    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof CoreApiUser) {
            throw new UnsupportedUserException();
        }

        return $user;
    }

    #[\Override]
    public function supportsClass(string $class): bool
    {
        return CoreApiUser::class === $class;
    }

    public function loadUserByUsername(string $username): UserInterface
    {
        return $this->loadUserByIdentifier($username);
    }

    #[\Override]
    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        try {
            $this->coreApi->setToken($identifier);
            $profile = $this->coreApi->user()->getProfileAuthenticated();

            return new CoreApiUser($profile, $identifier);
        } catch (\Throwable $e) {
            $this->logger->error($e->getMessage(), ['trace' => $e->getTraceAsString(), 'code' => $e->getCode()]);
            throw new CustomUserMessageAuthenticationException('emsch.security.exception.error');
        }
    }
}
