<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Controller\UserApi;

use EMS\ClientHelperBundle\Helper\UserApi\TestService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final readonly class TestController
{
    public function __construct(private TestService $testService)
    {
    }

    public function __invoke(Request $request): JsonResponse
    {
        return $this->testService->test($request);
    }
}
