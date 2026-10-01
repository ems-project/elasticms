<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Controller;

use EMS\CoreBundle\Service\JobService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class LayoutController extends AbstractController
{
    public function __construct(
        private readonly JobService $jobService,
        private readonly string $templateNamespace,
    ) {
    }

    public function jobs(string $username): Response
    {
        return $this->render(
            \sprintf('@%s/layout/jobs-list.html.twig', $this->templateNamespace),
            [
                'jobs' => $this->jobService->findByUser($username),
            ]
        );
    }
}
