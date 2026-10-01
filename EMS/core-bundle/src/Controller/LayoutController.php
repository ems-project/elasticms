<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Controller;

use EMS\CommonBundle\Service\ElasticaService;
use EMS\CoreBundle\Core\UI\LayoutService;
use EMS\CoreBundle\Service\AssetExtractorService;
use EMS\CoreBundle\Service\JobService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

class LayoutController extends AbstractController
{
    public function __construct(
        private readonly LayoutService $layoutService,
        private readonly AssetExtractorService $assetExtractorService,
        private readonly ElasticaService $elasticaService,
        private readonly JobService $jobService,
        private readonly string $templateNamespace,
    ) {
    }

    public function sideMenu(?UserInterface $user = null): Response
    {
        $status = $this->elasticaService->getHealthStatus();
        if ('green' === $status) {
            $status = $this->assetExtractorService->getStatus();
        }

        return $this->render(
            \sprintf('@%s/layout/side-menu.html.twig', $this->templateNamespace),
            [
                'status' => $status,
                'menu' => $user ? $this->layoutService->getSidebarMenus($user) : [],
            ]
        );
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
