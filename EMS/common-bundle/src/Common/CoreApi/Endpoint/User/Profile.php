<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\CoreApi\Endpoint\User;

use EMS\CommonBundle\Contracts\CoreApi\Endpoint\User\ProfileInterface;

final class Profile implements ProfileInterface
{
    private readonly int $id;
    private readonly string $username;
    private readonly string $email;
    private readonly ?string $displayName;
    /** @var string[] */
    private readonly array $roles;
    /** @var string[] */
    private readonly array $circles;
    /** @var array<string, mixed> */
    private readonly array $userOptions;
    private ?\DateTimeImmutable $lastLogin = null;
    private ?\DateTimeImmutable $expirationDate = null;

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
        $this->userOptions = $data['userOptions'] ?? [];

        if (isset($data['lastLogin'])) {
            $lastLogin = \DateTimeImmutable::createFromFormat(\DateTimeImmutable::ATOM, $data['lastLogin']);
            $this->lastLogin = false !== $lastLogin ? $lastLogin : null;
        }
        if (isset($data['expirationDate'])) {
            $expirationDate = \DateTimeImmutable::createFromFormat(\DateTimeImmutable::ATOM, $data['expirationDate']);
            $this->expirationDate = false !== $expirationDate ? $expirationDate : null;
        }
    }

    #[\Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[\Override]
    public function getUsername(): string
    {
        return $this->username;
    }

    #[\Override]
    public function getEmail(): string
    {
        return $this->email;
    }

    #[\Override]
    public function getDisplayName(): ?string
    {
        return $this->displayName;
    }

    /**
     * @return string[]
     */
    #[\Override]
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * @return string[]
     */
    #[\Override]
    public function getCircles(): array
    {
        return $this->circles;
    }

    #[\Override]
    public function getLastLogin(): ?\DateTimeImmutable
    {
        return $this->lastLogin;
    }

    public function getExpirationDate(): ?\DateTimeImmutable
    {
        return $this->expirationDate;
    }

    /**
     * @return array<string, mixed>
     */
    #[\Override]
    public function getUserOptions(): array
    {
        return $this->userOptions;
    }
}
