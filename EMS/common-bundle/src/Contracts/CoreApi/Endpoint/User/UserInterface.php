<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Contracts\CoreApi\Endpoint\User;

use EMS\CommonBundle\Contracts\CoreApi\CoreApiExceptionInterface;

interface UserInterface
{
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

    public function proxyAuthenticate(string $username, ?string $email = null, ?string $group = null): ?string;

    public function resetPasswordUrl(string $username): string;

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): string;

    public function delete(string $username): bool;

    /**
     * @param array<string, mixed> $data
     */
    public function edit(array $data): bool;
}
