<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Security\Saml\User;

use Symfony\Component\Security\Core\User\UserInterface;

class SamlUser implements UserInterface
{
    public function __construct(private readonly string $identifier)
    {
    }

    public function getRoles(): array
    {
        return [];
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

    public function getUserIdentifier(): string
    {
        return $this->identifier;
    }

    public function getUsername(): string
    {
        return $this->identifier;
    }
}
