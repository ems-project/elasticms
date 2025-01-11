<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Controller;

use EMS\ClientHelperBundle\Helper\Cache\CacheHelper;
use EMS\ClientHelperBundle\Helper\Request\EmschRequest;
use Symfony\Component\HttpFoundation\Response;

final readonly class CacheController
{
    public function __construct(private CacheHelper $cacheHelper)
    {
    }

    public function getCacheHelper(): CacheHelper
    {
        return $this->cacheHelper;
    }

    public function __invoke(EmschRequest $request): Response
    {
        $response = $this->cacheHelper->getResponse($request->getEmschCacheKey());

        if (null === $response) {
            return new Response(null, Response::HTTP_CREATED);
        }

        return $response;
    }
}
