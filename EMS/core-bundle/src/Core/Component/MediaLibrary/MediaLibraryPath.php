<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Core\Component\MediaLibrary;

class MediaLibraryPath implements \Countable
{
    /**
     * @param string[] $value
     */
    public function __construct(public readonly array $value)
    {
    }

    public static function fromString(string $path): self
    {
        return new self(\array_filter(\explode('/', $path)));
    }

    public function getValue(): string
    {
        return '/'.\implode('/', $this->value);
    }

    public function count(): int
    {
        return \count($this->value);
    }

    public function getName(): string
    {
        return \basename($this->getValue());
    }

    public function setName(string $name): self
    {
        $path = $this->value;
        \array_pop($path);

        return new self([...$path, $name]);
    }

    public function parent(): ?self
    {
        $path = $this->value;
        \array_pop($path);

        return $path ? new self($path) : null;
    }
}
