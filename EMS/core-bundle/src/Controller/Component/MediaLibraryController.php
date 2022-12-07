<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Controller\Component;

use EMS\CoreBundle\Core\Component\MediaLibrary\MediaLibraryConfig;
use EMS\CoreBundle\Core\Component\MediaLibrary\MediaLibraryService;
use Symfony\Component\HttpFoundation\JsonResponse;

class MediaLibraryController
{
    public function __construct(private readonly MediaLibraryService $mediaLibraryService)
    {
    }

    public function getFiles(MediaLibraryConfig $config): JsonResponse
    {
        return new JsonResponse($this->mediaLibraryService->getFiles($config));
    }
}
