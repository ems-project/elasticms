<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Contracts\CoreApi\Endpoint\User;

use EMS\CommonBundle\Contracts\CoreApi\CoreApiExceptionInterface;

interface UserInterface
{
    /**
     * @throws CoreApiExceptionInterface
     */
    public function getProfileAuthenticated(): ProfileInterface;

    /**
     * @return ProfileInterface[]
     *
     * @throws CoreApiExceptionInterface
     */
    public function getProfiles(): array;

    /**
     * @param string[] $roles
     */
    public function proxyAuthenticate(string $username, ?string $email, array $roles = []): ?string;
}
