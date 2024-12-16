<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Security\CoreApi\User;

use EMS\CommonBundle\Contracts\CoreApi\Endpoint\User\ProfileInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class CoreApiUser implements UserInterface
{
    public ?string $displayName;
    public string $username;
    public string $email;
    /** @var string[] */
    public array $circles;

    public function __construct(
        public readonly ProfileInterface $profile,
        private readonly string $token,
    ) {
        $this->displayName = $this->profile->getDisplayName();
        $this->username = $this->profile->getUsername();
        $this->email = $this->profile->getEmail();
        $this->circles = $this->profile->getCircles();
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getRoles(): array
    {
        return $this->profile->getRoles();
    }

    public function getPassword(): ?string
    {
        return null;
    }

    public function eraseCredentials(): void
    {
    }

    public function getUserIdentifier(): string
    {
        return $this->profile->getUsername();
    }

    public function getUsername(): string
    {
        return $this->getUserIdentifier();
    }
}
