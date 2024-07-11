<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Controller\Dashboard;

use EMS\CoreBundle\Controller\CoreControllerTrait;
use EMS\CoreBundle\Core\Dashboard\DashboardManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class DashboardBrowserController extends AbstractController
{
    use CoreControllerTrait;

    public function __construct(private readonly DashboardManager $dashboardManager)
    {
    }

    public function __invoke(string $dashboardName): Response
    {
        $dashboard = $this->dashboardManager->getByName($dashboardName);

        try {
            return $this->render('@EMSCore/dashboard/browser/dashboard-browser-modal.html.twig', [
                'dashboard' => $dashboard,
            ]);
        } catch (\Throwable $e) {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);

            return $this->render('@EMSCore/dashboard/browser/dashboard-browser-modal-error.html.twig', [
                'exception' => $e,
                'dashboard' => $dashboard,
            ], $response);
        }
    }
}
