<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\CoreApi\Endpoint\User;

use EMS\CommonBundle\Common\CoreApi\Client;
use EMS\CommonBundle\Contracts\CoreApi\Endpoint\User\ProfileInterface;
use EMS\CommonBundle\Contracts\CoreApi\Endpoint\User\UserInterface;

final class User implements UserInterface
{
    private Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @return ProfileInterface[]
     */
    public function getProfiles(): array
    {
        $result = $this->client->get('/api/user-profiles');

        return \array_map(fn (array $data) => new Profile($data), $result->getData());
    }

    public function getProfileAuthenticated(): ProfileInterface
    {
        return new Profile($this->client->get('/api/user-profile')->getData());
    }
}
