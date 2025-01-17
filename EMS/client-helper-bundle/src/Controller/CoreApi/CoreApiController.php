<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Controller\CoreApi;

use EMS\CommonBundle\Contracts\Bridge\CoreBridgeInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

readonly class CoreApiController
{
    public function __construct(private CoreBridgeInterface $coreBridge)
    {
    }

    public function getVersions(): JsonResponse
    {
        return new JsonResponse($this->coreBridge->versions());
    }
}
