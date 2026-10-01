<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Core\UI;

use EMS\CoreBundle\Core\ContentType\ContentTypeRoles;
use EMS\CoreBundle\Core\Dashboard\DashboardManager;
use EMS\CoreBundle\Routes;
use EMS\CoreBundle\Service\ContentTypeService;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

use function Symfony\Component\Translation\t;

class LayoutService
{
    public function __construct(
        private readonly DashboardManager $dashboardManager,
        private readonly ContentTypeService $contentTypeService,
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
            $this->sidebarContentTypes($user),
        ]);
    }

    private function sidebarDashboards(UserInterface $user): ?Menu
    {
        $menu = new Menu(t('key.dashboards', [], 'emsco-core'));

        foreach ($this->dashboardManager->getVisibleSidebarDashboards() as $dashboard) {
            if (!$this->isGranted($dashboard->getRole())) {
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

        return $menu->hasChildren() ? $menu : null;
    }

    private function sidebarContentTypes(UserInterface $user): ?Menu
    {
        $counters = $this->contentTypeService->getDraftCounters($user);
        $menu = new Menu(t('key.content_types', [], 'emsco-core'));

        foreach ($this->contentTypeService->getActiveContentTypes() as $contentType) {
            $roles = $contentType->getRoles();

            if (!$contentType->getRootContentType() && !$this->isGranted($roles[ContentTypeRoles::VIEW])) {
                continue;
            }

            [$route, $routeParameters] = $this->contentTypeService->getRedirectOverviewRoute($contentType);
            $entry = new MenuEntry(
                label: $contentType->getPluralName($user),
                icon: $contentType->getIcon() ?? 'fa fa-book',
                route: $route,
                routeParameters: $routeParameters,
                color: $contentType->getColor()
            );

            $counter = $counters[$contentType->getId()] ?? null;
            if (null !== $counter) {
                $entry->setBadge((string) $counter);
            }

            foreach ($contentType->getViews() as $view) {
                if ('ems.view.data_link' === $view->getType() || !$this->isGranted($view->getRole())) {
                    continue;
                }

                $entry->addChild(
                    label: $view->getLabel($user),
                    icon: $view->getIcon() ?? '',
                    route: $view->isPublic() ? Routes::DATA_PUBLIC_VIEW : Routes::DATA_PRIVATE_VIEW,
                    routeParameters: ['viewId' => $view->getId()]
                );
            }

            if ($entry->hasBadge()
                && $contentType->giveEnvironment()->getManaged()
                && $this->isGranted($contentType->role(ContentTypeRoles::EDIT))) {
                $entry
                    ->addChild(
                        label: t('key.draft_in_progress', [], 'emsco-core'),
                        icon: 'fa fa-fire',
                        route: Routes::DRAFT_IN_PROGRESS,
                        routeParameters: ['contentTypeId' => $contentType->getId()]
                    )
                    ->setBadge($entry->getBadge(), $contentType->getColor());
            }

            if ($this->isGranted($roles[ContentTypeRoles::SHOW_LINK_CREATE]) && $this->isGranted($roles[ContentTypeRoles::CREATE])) {
                $entry->addChild(
                    label: t('action.new_entity_name', $contentType->getSingularNameTranslation($user)->getParameters(), 'emsco-core'),
                    icon: 'fa fa-plus',
                    route: Routes::DATA_ADD,
                    routeParameters: ['contentType' => $contentType->getId()]
                );
            }

            if ($this->isGranted($roles[ContentTypeRoles::TRASH])) {
                $entry->addChild(
                    label: t('key.trash', [], 'emsco-core'),
                    icon: 'fa fa-trash',
                    route: Routes::DATA_TRASH,
                    routeParameters: ['contentType' => $contentType->getId()]
                );
            }

            if ($entry->hasChildren()) {
                $menu->addMenuEntry($entry);
            }
        }

        return $menu->hasChildren() ? $menu : null;
    }

    private function isGranted(?string $role): bool
    {
        return null === $role || $this->authorizationChecker->isGranted($role);
    }
}
