<?php

declare(strict_types=1);

namespace App\Client\Data;

/**
 * @implements \IteratorAggregate<array<mixed>>
 */
class Data implements \Countable, \IteratorAggregate
{
    /** @var array<mixed> */
    private array $data;

    /**
     * @param array<mixed> $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function slice(?int $offset, ?int $length = null): void
    {
        $this->data = \array_slice($this->data, $offset ?? 0, $length);
    }

    /**
     * @return \ArrayIterator<int, array<mixed>>
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->data);
    }

    public function count(): int
    {
        return \count($this->data);
    }

    public function searchAndReplace(int $columnIndex, string $search, string $replace): void
    {
        foreach ($this->data as &$row) {
            if (isset($row[$columnIndex]) && $search === (string) $row[$columnIndex]) {
                $row[$columnIndex] = $replace;
            }
        }
    }

    public function filter(callable $callback): void
    {
        $this->data = \array_filter($this->data, $callback);
    }

    public function groupByColumn(int $columnIndex): void
    {
        $data = [];
        foreach ($this->data as $record) {
            $key = $record[$columnIndex] ?? null;
            if (!\is_string($key) && \is_int($key)) {
                throw new \RuntimeException('Unexpected non indexable value');
            }
            $data[$key][] = $record;
        }
        $this->data = $data;
    }
}
