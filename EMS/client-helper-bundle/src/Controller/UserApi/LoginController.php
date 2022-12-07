<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Controller\UserApi;

use EMS\ClientHelperBundle\Helper\UserApi\AuthService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class LoginController
{
    public function __construct(private readonly AuthService $authService)
    {
    }

    public function __invoke(Request $request): JsonResponse
    {
        return $this->authService->getUserAuthToken($request);
    }
}
