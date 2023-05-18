<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\PropertyAccess;

class PathProperty
{
    /** @var PathPropertyElement[] */
    private array $elements = [];
    private int $length;

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

    public function getLength(): int
    {
        return $this->length;
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
}
