<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Security\Sso\User;

use EMS\Helpers\Standard\Text;
use Symfony\Component\Security\Core\User\UserInterface;

class SsoUser implements UserInterface
{
    public function __construct(
        private readonly string $identifier,
        public readonly ?string $email = null
    ) {
    }

    #[\Override]
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

    #[\Override]
    public function eraseCredentials(): void
    {
    }

    #[\Override]
    public function getUserIdentifier(): string
    {
        $trimmed = Text::superTrim($this->identifier);

        if ('' === $trimmed || $this->identifier !== $trimmed) {
            throw new \LogicException('User identifier cannot be empty or start/end with a space.');
        }

        return $this->identifier;
    }

    public function getUsername(): string
    {
        return $this->identifier;
    }
}
