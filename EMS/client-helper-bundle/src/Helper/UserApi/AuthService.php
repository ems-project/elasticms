<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\UserApi;

use EMS\Helpers\Standard\Json;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final readonly class AuthService
{
    public function __construct(private ClientFactory $client)
    {
    }

    public function getUserAuthToken(Request $request): JsonResponse
    {
        $credentials = [
            'username' => $request->request->getString('username'),
            'password' => $request->request->getString('password'),
        ];

        $client = $this->client->createClient(['Content-Type' => 'application/json']);
        $response = $client->post('auth-token', ['body' => Json::encode($credentials)]);

        return new JsonResponse($response->getBody()->getContents());
    }
}
