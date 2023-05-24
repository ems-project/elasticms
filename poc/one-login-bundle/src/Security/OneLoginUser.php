<?php

declare(strict_types=1);

namespace EMS\OneLoginBundle\Security;

use Symfony\Component\Security\Core\User\UserInterface;

class OneLoginUser implements UserInterface
{
    public function __construct(private readonly string $username) {}

    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }

    public function getPassword(): ?string
    {
        return null;
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function eraseCredentials(): void
    {
    }

    public function getUsername(): string
    {
        return $this->username;
    }
}