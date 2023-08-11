<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Controller\Component;

use EMS\CoreBundle\Core\Component\JsonMenuNested\Config\JsonMenuNestedConfig;
use EMS\CoreBundle\Core\Component\JsonMenuNested\JsonMenuNestedService;
use Symfony\Component\HttpFoundation\JsonResponse;

class JsonMenuNestedController
{
    public function __construct(
        private readonly JsonMenuNestedService $jsonMenuNestedService
    ) {
    }

    public function getStructure(JsonMenuNestedConfig $config, ?string $parentId = null): JsonResponse
    {
        return new JsonResponse($this->jsonMenuNestedService->getStructure($config, $parentId));
    }
}
