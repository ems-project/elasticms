<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\UserApi;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class AuthService
{
    private ClientFactory $client;

    public function __construct(ClientFactory $client)
    {
        $this->client = $client;
    }

    public function getUserAuthToken(Request $request): JsonResponse
    {
        $credentials = [
            'username' => $request->get('username'),
            'password' => $request->get('password'),
        ];

        $client = $this->client->createClient(['Content-Type' => 'application/json']);
        $response = $client->post('auth-token', ['body' => \json_encode($credentials, JSON_THROW_ON_ERROR)]);

        return new JsonResponse($response->getBody()->getContents());
    }
}
