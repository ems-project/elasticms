<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Controller\CoreApi;

use EMS\CommonBundle\Contracts\Bridge\Core\CoreBridgeInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

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
}
