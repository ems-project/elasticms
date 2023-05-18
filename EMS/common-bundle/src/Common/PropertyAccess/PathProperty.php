<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\PropertyAccess;

/**
 * @implements \Iterator<PathPropertyElement>
 */
class PathProperty implements \Iterator, \Countable
{
    /** @var PathPropertyElement[] */
    private array $elements = [];
    private int $length;
    private int $index = 0;

    public function __construct(private readonly string $pathAsString)
    {
        $remaining = $pathAsString;
        $position = 0;
        $pattern = '/^(?P<match>\[(?P<element>((?P<operators>[^\]]+):)?(?P<slug>[^\]]+))\])(?P<remaining>.*)/';

        while (\preg_match($pattern, $remaining, $matches)) {
            $this->elements[] = new PathPropertyElement($matches['slug'], '' === $matches['operators'] ? [] : \explode(':', $matches['operators']));
            $remaining = $matches['remaining'];
            $position += \strlen($matches['match']);
        }

        if ('' !== $remaining) {
            throw new InvalidPathPropertyException(\sprintf('Could not parse property path "%s". Unexpected token "%s" at position %d.', $pathAsString, $remaining[0], $position));
        }

        $this->length = \count($this->elements);
    }

    /**
     * @return PathPropertyElement[]
     */
    public function getElements(): array
    {
        return $this->elements;
    }

    public function getPathAsString(): string
    {
        return $this->pathAsString;
    }

    public function next(): void
    {
        ++$this->index;
    }

    public function current(): PathPropertyElement
    {
        if (!isset($this->elements[$this->index])) {
            throw new \RuntimeException(\sprintf('Out of bounds exception: try to access %d, not in range [0;%d]', $this->index, $this->length - 1));
        }

        return $this->elements[$this->index];
    }

    public function key(): int
    {
        return $this->index;
    }

    public function valid(): bool
    {
        return isset($this->elements[$this->index]);
    }

    public function rewind(): void
    {
        $this->index = 0;
    }

    public function count(): int
    {
        return $this->length;
    }

    public function last(): bool
    {
        return $this->index + 1 === $this->length;
    }
}
