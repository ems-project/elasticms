<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Controller;

use EMS\CoreBundle\Core\Dashboard\DashboardManager;
use EMS\CoreBundle\Core\Dashboard\DashboardService;
use EMS\CoreBundle\Entity\Dashboard;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final class DashboardController extends AbstractController
{
    public function __construct(private readonly DashboardManager $dashboardManager, private readonly DashboardService $dashboardService)
    {
    }

    public function quickSearch(): Response
    {
        $dashboard = $this->dashboardManager->getDefinition(Dashboard::DEFINITION_QUICK_SEARCH);
        if (null === $dashboard) {
            return $this->redirectToRoute('notifications.inbox');
        }
        if (!$this->isGranted($dashboard->getRole())) {
            throw new AccessDeniedHttpException();
        }
        $dashboardService = $this->dashboardService->get($dashboard->getType());

        return $dashboardService->getResponse($dashboard);
    }

    public function dashboard(?string $name = null): Response
    {
        if (null === $name) {
            $dashboard = $this->dashboardManager->getDefinition(Dashboard::DEFINITION_LANDING_PAGE);
        } else {
            $dashboard = $this->dashboardManager->getByName($name);
        }
        if (null === $dashboard) {
            return $this->redirectToRoute('notifications.inbox');
        }
        if (!$this->isGranted($dashboard->getRole())) {
            throw new AccessDeniedHttpException();
        }
        $dashboardService = $this->dashboardService->get($dashboard->getType());

        return $dashboardService->getResponse($dashboard);
    }
}
