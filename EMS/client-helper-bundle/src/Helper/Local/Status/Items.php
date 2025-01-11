<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Local\Status;

/**
 * @implements \IteratorAggregate<Item>
 */
final class Items implements \IteratorAggregate, \Countable
{
    /** @var Item[] */
    private array $items = [];

    /**
     * @param Item[] $items
     */
    public function __construct(array $items)
    {
        foreach ($items as $item) {
            $this->items[$item->getKey()] = $item;
        }
    }

    public function filter(callable $callback): self
    {
        $items = $this->items;

        return new self(\array_filter($items, $callback));
    }

    /**
     * @return \ArrayIterator<int, Item>|Item[]
     */
    #[\Override]
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->items);
    }

    #[\Override]
    public function count(): int
    {
        return \count($this->items);
    }

    public function add(Item $item): void
    {
        $items = $this->items;
        $items[$item->getKey()] = $item;

        \ksort($items);

        $this->items = $items;
    }

    public function hasItem(string $key): bool
    {
        return isset($this->items[$key]);
    }

    public function getItem(string $key): Item
    {
        if (!isset($this->items[$key])) {
            throw new \RuntimeException(\sprintf('Could not found item with key %s', $key));
        }

        return $this->items[$key];
    }
}
