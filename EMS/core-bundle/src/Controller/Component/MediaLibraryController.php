<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Controller\Component;

use EMS\CoreBundle\Core\Component\MediaLibrary\MediaLibraryConfig;
use Symfony\Component\HttpFoundation\JsonResponse;

class MediaLibraryController
{
    public function getFiles(MediaLibraryConfig $config): JsonResponse
    {
        return new JsonResponse([]);
    }
}
