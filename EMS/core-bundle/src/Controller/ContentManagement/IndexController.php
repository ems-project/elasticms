<?php

namespace EMS\CoreBundle\Controller\ContentManagement;

use EMS\CoreBundle\Routes;
use EMS\CoreBundle\Service\IndexService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;

class IndexController extends AbstractController
{
    public function __construct(private readonly IndexService $indexService)
    {
    }

    public function deleteOrphansIndexesAction(): RedirectResponse
    {
        $this->indexService->deleteOrphanIndexes();

        return $this->redirectToRoute(Routes::ADMIN_ENVIRONMENT_INDEX);
    }
}
