<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Contracts\CoreApi\Endpoint\User;

use EMS\CommonBundle\Contracts\CoreApi\CoreApiExceptionInterface;

interface UserInterface
{
    public function authenticate(string $username, ?string $email): ?string;

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
}
