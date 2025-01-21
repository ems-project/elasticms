<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Controller\CoreApi;

use EMS\CommonBundle\Contracts\Bridge\Core\CoreBridgeInterface;
use EMS\Helpers\Standard\Json;
use EMS\Helpers\Standard\Type;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

readonly class CoreApiController
{
    public function __construct(private CoreBridgeInterface $coreBridge)
    {
    }

    public function api(): void
    {
        // keep for generating the base url for calling the api.
    }

    public function getVersions(): JsonResponse
    {
        return new JsonResponse($this->coreBridge->versions());
    }

    public function autoSave(Request $request, string $contentType, int $revisionId): JsonResponse
    {
        return new JsonResponse([
            'success' => $this->coreBridge->data($contentType)->autoSave(
                revisionId: $revisionId,
                data: Json::decode(Type::string($request->getContent()))
            ),
        ]);
    }
}
