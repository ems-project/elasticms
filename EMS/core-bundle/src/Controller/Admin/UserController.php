<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Controller\Admin;

use EMS\CoreBundle\Core\User\UserManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class UserController extends AbstractController
{
    public function __construct(
        private readonly UserManager $userManager,
    ) {
    }

    public function resetPasswordUrl(Request $request): JsonResponse
    {
        $username = $request->request->getString('username') ?: $request->query->getString('username');
        if ('' === $username) {
            throw new BadRequestHttpException('The "username" parameter is required.');
        }

        $user = $this->userManager->requestResetPassword($username);
        if (null === $user || null === $user->getConfirmationToken()) {
            throw new BadRequestHttpException('User not found.');
        }
        $token = $user->getConfirmationToken();

        return new JsonResponse([
            'url' => $this->generateUrl('emsco_user_resetting_reset', [
                'token' => $token,
            ]),
            'token' => $token,
        ]);
    }
}
