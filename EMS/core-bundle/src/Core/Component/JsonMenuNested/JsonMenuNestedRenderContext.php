<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Core\Component\JsonMenuNested;

use EMS\CommonBundle\Json\JsonMenuNested;

class JsonMenuNestedRenderContext
{
    public ?JsonMenuNested $activeItem;
    /** @var array<string, JsonMenuNested> */
    public array $loadParents = [];
    /** @var array<string, JsonMenuNested> */
    public array $loadItems = [];

    /**
     * @param string[] $loadParentIds
     */
    public function __construct(
        private readonly JsonMenuNested $menu,
        ?string $activeItemId,
        ?string $loadChildrenId,
        array $loadParentIds
    ) {
        $this->addActiveItem($menu);
        $this->activeItem = $activeItemId ? $menu->getItemById($activeItemId) : $menu;

        $loadChildren = $loadChildrenId ? $this->menu->getItemById($loadChildrenId) : null;
        if ($loadChildren) {
            foreach ($loadChildren as $loadChild) {
                if ($loadChild->hasChildren()) {
                    $this->addParent($loadChild);
                }
            }
        }

        foreach ($loadParentIds as $loadParentId) {
            $this->addParent($menu->getItemById($loadParentId));
        }
    }

    /**
     * @return string[]
     */
    public function getItemIds(): array
    {
        return \array_values(\array_map(static fn (JsonMenuNested $item) => $item->getId(), $this->loadItems));
    }

    /**
     * @return string[]
     */
    public function getParentIds(): array
    {
        return \array_values(\array_map(static fn (JsonMenuNested $item) => $item->getId(), $this->loadParents));
    }

    private function addParent(JsonMenuNested $parent = null): void
    {
        if (null === $parent) {
            return;
        }

        if (\array_key_exists($parent->getId(), $this->loadParents)) {
            return;
        }

        $this->loadParents[$parent->getId()] = $parent;
        $this->addActiveItem($parent);
    }

    private function addActiveItem(JsonMenuNested $item): void
    {
        if (!$item->isRoot()) {
            $this->loadItems[$item->getId()] = $item;
        }

        foreach ($item->getChildren() as $child) {
            $this->loadItems[$child->getId()] = $child;
        }
    }
}
