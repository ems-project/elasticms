<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\CoreApi\Endpoint\User;

use EMS\CommonBundle\Common\CoreApi\Client;
use EMS\CommonBundle\Contracts\CoreApi\Endpoint\User\ProfileInterface;
use EMS\CommonBundle\Contracts\CoreApi\Endpoint\User\UserInterface;

final readonly class User implements UserInterface
{
    public function __construct(private Client $client)
    {
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
    public function getProfileAuthenticated(): ProfileInterface
    {
        return new Profile($this->client->get('/api/user-profile')->getData());
    }
}
