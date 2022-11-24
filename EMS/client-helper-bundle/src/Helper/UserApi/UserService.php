<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\UserApi;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class UserService
{
    private ClientFactory $client;

    public function __construct(ClientFactory $client)
    {
        $this->client = $client;
    }

    public function getUsers(Request $request): JsonResponse
    {
        $client = $this->client->createClient(['X-Auth-Token' => $request->headers->get('X-Auth-Token')]);
        $response = $client->get('/api/user-profiles');

        return JsonResponse::fromJsonString($response->getBody()->getContents());
    }

    public function getProfile(Request $request): JsonResponse
    {
        $client = $this->client->createClient(['X-Auth-Token' => $request->headers->get('X-Auth-Token')]);
        $response = $client->get('/api/user-profile');

        return JsonResponse::fromJsonString($response->getBody()->getContents());
    }
}
