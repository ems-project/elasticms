<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Controller\UserApi;

use EMS\ClientHelperBundle\Helper\UserApi\FileService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final readonly class FileController
{
    public function __construct(private FileService $fileService)
    {
    }

    public function create(Request $request): JsonResponse
    {
        return $this->fileService->uploadFile($request);
    }
}
