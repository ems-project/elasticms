<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Controller\Component;

use EMS\CoreBundle\Core\Component\JsonMenuNested\Config\JsonMenuNestedConfig;
use EMS\CoreBundle\Core\Component\JsonMenuNested\JsonMenuNestedService;
use EMS\Helpers\Standard\Json;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

class JsonMenuNestedController
{
    public function __construct(
        private readonly JsonMenuNestedService $jsonMenuNestedService
    ) {
    }

    public function getStructure(Request $request, JsonMenuNestedConfig $config, ?string $parentId = null): JsonResponse
    {
        if ($request->isMethod(Request::METHOD_POST)) {
            $data = Json::decode($request->getContent());
        }

        $load = $data['load'] ?? [];
        $structure = $this->jsonMenuNestedService->getStructure($config, $parentId, $load);

        return new JsonResponse(['structure' => $structure]);
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
