<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Core\UI;

use EMS\CoreBundle\Core\Dashboard\DashboardManager;
use EMS\CoreBundle\Routes;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

use function Symfony\Component\Translation\t;

class LayoutService
{
    public function __construct(
        private readonly DashboardManager $dashboardManager,
        private readonly AuthorizationCheckerInterface $authorizationChecker,
    ) {
    }

    /**
     * @return Menu[]
     */
    public function getSidebarMenus(UserInterface $user): array
    {
        return \array_filter([
            $this->sidebarDashboards($user),
        ]);
    }

    private function sidebarDashboards(UserInterface $user): ?Menu
    {
        if ([] === $dashboards = $this->dashboardManager->getVisibleSidebarDashboards()) {
            return null;
        }

        $menu = new Menu(t('key.dashboards', [], 'emsco-core'));

        foreach ($dashboards as $dashboard) {
            if (!$this->authorizationChecker->isGranted($dashboard->getRole())) {
                continue;
            }

            $menu->addChild(
                label: $dashboard->getLabel($user),
                icon: $dashboard->getIcon(),
                route: Routes::DASHBOARD,
                routeParameters: ['name' => $dashboard->getName()],
                color: $dashboard->getColor()
            );
        }

        return $menu;
    }
}
