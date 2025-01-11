<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Controller\UserApi;

use EMS\ClientHelperBundle\Helper\UserApi\DocumentService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final readonly class DocumentController
{
    public function __construct(private DocumentService $documentService)
    {
    }

    public function show(string $contentType, string $ouuid, Request $request): JsonResponse
    {
        return $this->documentService->getDocument($contentType, $ouuid, $request);
    }

    public function create(string $contentType, Request $request): JsonResponse
    {
        return $this->documentService->createDocument($contentType, $request);
    }

    public function update(string $contentType, string $ouuid, Request $request): JsonResponse
    {
        return $this->documentService->updateDocument($contentType, $ouuid, $request);
    }

    public function merge(string $contentType, string $ouuid, Request $request): JsonResponse
    {
        return $this->documentService->mergeDocument($contentType, $ouuid, $request);
    }
}
