<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Controller;

use EMS\CommonBundle\Service\ElasticaService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final readonly class ProbeController
{
    public function __construct(private ElasticaService $elasticaService)
    {
    }

    public function readiness(): Response
    {
        $version = $this->elasticaService->getVersion();

        return new JsonResponse([
            'cluster_version' => $version,
        ]);
    }

    public function liveness(): Response
    {
        return new JsonResponse([
            'live' => true,
        ]);
    }
}
