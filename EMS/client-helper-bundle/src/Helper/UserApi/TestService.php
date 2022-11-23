<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\UserApi;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class TestService
{
    private ClientFactory $client;
    private LoggerInterface $logger;

    public function __construct(ClientFactory $client, LoggerInterface $logger)
    {
        $this->client = $client;
        $this->logger = $logger;
    }

    public function test(Request $request): JsonResponse
    {
        try {
            $client = $this->client->createClient(['X-Auth-Token' => $request->headers->get('X-Auth-Token')]);
            $response = $client->get('/api/test');
            $json = \json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);

            $status = ($json['success']) ? '{"success": true}' : '{"success": false}';

            return JsonResponse::fromJsonString($status);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());

            return JsonResponse::fromJsonString('{"success": false}');
        }
    }
}
