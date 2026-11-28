<?php

declare(strict_types=1);

namespace App\Admin\Controller;

use EMS\CoreBundle\Routes;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route(path: '/', name: 'homepage')]
    public function index(): RedirectResponse
    {
        return $this->redirectToRoute(Routes::DASHBOARD_HOME);
    }
}
