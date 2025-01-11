<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Controller\UserApi;

use EMS\ClientHelperBundle\Helper\UserApi\UserService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final readonly class UserController
{
    public function __construct(private UserService $userService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        return $this->userService->getUsers($request);
    }
}
