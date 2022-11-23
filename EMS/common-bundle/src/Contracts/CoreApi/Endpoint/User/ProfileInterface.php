<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Contracts\CoreApi\Endpoint\User;

interface ProfileInterface
{
    public function getId(): int;

    public function getUsername(): string;

    public function getEmail(): string;

    public function getDisplayName(): ?string;

    /**
     * @return string[]
     */
    public function getRoles(): array;

    /**
     * @return string[]
     */
    public function getCircles(): array;

    public function getLastLogin(): ?\DateTimeImmutable;
}
