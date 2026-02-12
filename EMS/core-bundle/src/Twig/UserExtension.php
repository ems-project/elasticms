<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Twig;

use EMS\CoreBundle\Core\User\UserList;
use EMS\CoreBundle\Repository\UserRepository;
use Twig\Attribute\AsTwigFunction;

readonly class UserExtension
{
    public function __construct(private UserRepository $userRepository)
    {
    }

    #[AsTwigFunction(name: 'emsco_users_enabled')]
    public function getUsersEnabled(): UserList
    {
        return $this->userRepository->getUsersEnabled();
    }
}
