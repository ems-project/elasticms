<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\CoreApi\Endpoint\User;

use EMS\CommonBundle\Contracts\CoreApi\Endpoint\User\ProfileInterface;

final class Profile implements ProfileInterface
{
    private int $id;
    private string $username;
    private string $email;
    private ?string $displayName;
    /** @var string[] */
    private array $roles;
    /** @var string[] */
    private array $circles;
    private ?\DateTimeImmutable $lastLogin = null;

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(array $data)
    {
        $this->id = $data['id'];
        $this->username = $data['username'];
        $this->email = $data['email'];
        $this->displayName = $data['displayName'] ?? null;
        $this->roles = $data['roles'] ?? [];
        $this->circles = $data['circles'] ?? [];

        if (isset($data['lastLogin'])) {
            $lastLogin = \DateTimeImmutable::createFromFormat(\DateTimeImmutable::ATOM, $data['lastLogin']);
            $this->lastLogin = false !== $lastLogin ? $lastLogin : null;
        }
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getDisplayName(): ?string
    {
        return $this->displayName;
    }

    /**
     * @return string[]
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * @return string[]
     */
    public function getCircles(): array
    {
        return $this->circles;
    }

    public function getLastLogin(): ?\DateTimeImmutable
    {
        return $this->lastLogin;
    }
}
