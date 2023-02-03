<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Form\Data;

/**
 * @implements \IteratorAggregate<TableItemAction>
 */
final class TableItemActionCollection implements \IteratorAggregate, \Countable
{
    /** @var TableItemAction[] */
    private array $itemActions = [];

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->itemActions);
    }

    public function count(): int
    {
        return \count($this->itemActions);
    }

    /**
     * @param array<mixed> $routeParameters
     */
    public function addItemGetAction(string $route, string $labelKey, string $icon, array $routeParameters = []): TableItemAction
    {
        $action = TableItemAction::getAction($route, $labelKey, $icon, $routeParameters);
        $this->itemActions[] = $action;

        return $action;
    }

    /**
     * @param array<string, mixed> $routeParameters
     */
    public function addItemPostAction(string $route, string $labelKey, string $icon, string $messageKey, array $routeParameters = []): TableItemAction
    {
        $action = TableItemAction::postAction($route, $labelKey, $icon, $messageKey, $routeParameters);
        $this->itemActions[] = $action;

        return $action;
    }

    /**
     * @param array<string, string> $routeParameters
     */
    public function addDynamicItemPostAction(string $route, string $labelKey, string $icon, string $messageKey, array $routeParameters = []): TableItemAction
    {
        $action = TableItemAction::postDynamicAction($route, $labelKey, $icon, $messageKey, $routeParameters);
        $this->itemActions[] = $action;

        return $action;
    }

    /**
     * @param array<string, string> $routeParameters
     */
    public function addDynamicItemGetAction(string $route, string $labelKey, string $icon, array $routeParameters = []): TableItemAction
    {
        $action = TableItemAction::getDynamicAction($route, $labelKey, $icon, $routeParameters);
        $this->itemActions[] = $action;

        return $action;
    }
}
