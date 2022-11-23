<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\UserApi;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class DocumentService
{
    private ClientFactory $client;

    public function __construct(ClientFactory $client)
    {
        $this->client = $client;
    }

    public function getDocument(string $contentType, string $ouuid, Request $request): JsonResponse
    {
        $client = $this->client->createClient(['X-Auth-Token' => $request->headers->get('X-Auth-Token')]);
        $response = $client->get(\sprintf('/api/data/%s/%s', $contentType, $ouuid));

        return JsonResponse::fromJsonString($response->getBody()->getContents());
    }

    public function createDocument(string $contentType, Request $request): JsonResponse
    {
        $endpoint = \sprintf('api/data/%s/draft', $contentType);

        return $this->createAndFinalizeDraft($contentType, $request, $endpoint);
    }

    public function updateDocument(string $contentType, string $ouuid, Request $request): JsonResponse
    {
        $endpoint = \sprintf('/api/data/%s/replace/%s', $contentType, $ouuid);

        return $this->createAndFinalizeDraft($contentType, $request, $endpoint);
    }

    public function mergeDocument(string $contentType, string $ouuid, Request $request): JsonResponse
    {
        $endpoint = \sprintf('/api/data/%s/merge/%s', $contentType, $ouuid);

        return $this->createAndFinalizeDraft($contentType, $request, $endpoint);
    }

    private function createAndFinalizeDraft(string $contentType, Request $request, string $endpoint): JsonResponse
    {
        $client = $this->client->createClient(['X-Auth-Token' => $request->headers->get('X-Auth-Token')]);

        $body = $request->getContent();
        if (!\is_string($body)) {
            throw new NotFoundHttpException('JSON file not found');
        }

        $draftResponse = $client->post(
            $endpoint,
            \compact('body')
        );

        $draft = \json_decode($draftResponse->getBody()->getContents(), true);

        $finalizeUrl = \sprintf('api/data/%s/finalize/%d', $contentType, $draft['revision_id']);
        $finalizeResponse = $client->post($finalizeUrl);

        return JsonResponse::fromJsonString($finalizeResponse->getBody()->getContents());
    }
}
