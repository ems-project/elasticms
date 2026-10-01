<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Twig\Components;

use EMS\CommonBundle\Service\ElasticaService;
use EMS\CoreBundle\Core\UI\LayoutService;
use EMS\CoreBundle\Core\UI\Menu;
use EMS\CoreBundle\Service\AssetExtractorService;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class LayoutSidebarComponent
{
    public function __construct(
        private readonly LayoutService $layoutService,
        private readonly ElasticaService $elasticaService,
        private readonly AssetExtractorService $assetExtractorService,
        private readonly Security $security,
    ) {
    }

    public function getStatus(): string
    {
        $status = $this->elasticaService->getHealthStatus();

        return 'green' === $status ? $this->assetExtractorService->getStatus() : $status;
    }

    /**
     * @return Menu[]
     */
    public function getMenus(): array
    {
        $user = $this->security->getUser();

        return $user instanceof UserInterface ? $this->layoutService->getSidebarMenus($user) : [];
    }
}
