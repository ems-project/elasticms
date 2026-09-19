<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Core\Dashboard\Services;

use EMS\CoreBundle\Entity\Dashboard;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class LegacySearch implements DashboardInterface
{
    public function __construct(private readonly Environment $twig, private readonly string $templateNamespace)
    {
    }

    public function getResponse(Dashboard $dashboard): Response
    {
        $response = new Response();
        $response->setContent($this->twig->render(\sprintf('@%s/dashboard/legacy-search/render.html.twig', $this->templateNamespace), [
            'dashboard' => $dashboard,
            'options' => $dashboard->getOptions(),
        ]));

        return $response;
    }
}
