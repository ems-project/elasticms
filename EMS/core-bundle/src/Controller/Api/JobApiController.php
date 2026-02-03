<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Controller\Api;

use EMS\CoreBundle\Service\JobService;
use EMS\Helpers\Standard\Json;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\User\UserInterface;

class JobApiController
{
    public function __construct(
        private readonly JobService $jobService
    ) {
    }

    public function create(Request $request, UserInterface $user): JsonResponse
    {
        $content = Json::decode($request->getContent());
        $command = $content['command'] ?? null;

        if (null === $command) {
            throw new BadRequestHttpException('Command not found');
        }

        $job = $this->jobService->createCommand($user, $command);

        return new JsonResponse([
            'success' => true,
            'jobId' => (string) $job->getId(),
        ]);
    }
}
