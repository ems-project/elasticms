<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\CoreApi\Endpoint\User;

use EMS\CommonBundle\Common\CoreApi\Client;
use EMS\CommonBundle\Contracts\CoreApi\Endpoint\User\ProfileInterface;
use EMS\CommonBundle\Contracts\CoreApi\Endpoint\User\UserInterface;
use EMS\Helpers\Standard\Type;

final readonly class User implements UserInterface
{
    public function __construct(private Client $client)
    {
    }

    #[\Override]
    public function getProfileAuthenticated(): ProfileInterface
    {
        return new Profile($this->client->get('/api/user-profile')->getData());
    }

    /**
     * @return ProfileInterface[]
     */
    #[\Override]
    public function getProfiles(): array
    {
        $result = $this->client->get('/api/user-profiles');

        return \array_map(fn (array $data) => new Profile($data), $result->getData());
    }

    #[\Override]
    public function proxyAuthenticate(string $username, ?string $email = null, ?string $group = null): ?string
    {
        $response = $this->client->post('/api/user/proxy-authenticate', [
            'username' => $username,
            'email' => $email,
            'group' => $group,
        ]);

        return $response->getData()['token'] ?? null;
    }

    public function resetPasswordUrl(string $username): string
    {
        $response = $this->client->post('/api/admin/user/reset-password', [
            'username' => $username,
        ]);

        return Type::string($response->getData()['url'] ?? null);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): string
    {
        $response = $this->client->post('/api/admin/user/create', $data);

        return Type::string($response->getData()['url'] ?? null);
    }

    public function delete(string $username): bool
    {
        $response = $this->client->post('/api/admin/user/delete', [
            'username' => $username,
        ]);

        return Type::bool($response->getData()['success'] ?? false);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function edit(array $data): bool
    {
        $response = $this->client->post('/api/admin/user/edit', $data);

        return Type::bool($response->getData()['success'] ?? false);
    }
}
