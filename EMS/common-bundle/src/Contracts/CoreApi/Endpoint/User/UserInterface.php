<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Contracts\CoreApi\Endpoint\User;

use EMS\CommonBundle\Contracts\CoreApi\CoreApiExceptionInterface;

interface UserInterface
{
    /**
     * @return ProfileInterface[]
     *
     * @throws CoreApiExceptionInterface
     */
    public function getProfiles(): array;

    /**
     * @throws CoreApiExceptionInterface
     */
    public function getProfileAuthenticated(): ProfileInterface;
}
