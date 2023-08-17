<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Controller\Component;

use EMS\CoreBundle\Core\Component\JsonMenuNested\Config\JsonMenuNestedConfig;
use EMS\CoreBundle\Core\Component\JsonMenuNested\JsonMenuNestedService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

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

    public function itemDelete(Request $request, JsonMenuNestedConfig $config, string $itemId): JsonResponse
    {
        $result = $this->jsonMenuNestedService->itemDelete($config, $itemId);
        $this->clearFlashes($request);

        return new JsonResponse($result);
    }

    private function clearFlashes(Request $request): void
    {
        /** @var Session $session */
        $session = $request->getSession();
        $session->getFlashBag()->clear();
    }
}
