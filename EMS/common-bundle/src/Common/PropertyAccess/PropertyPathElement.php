<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\PropertyAccess;

class PropertyPathElement
{
    /**
     * @param string[] $operators
     */
    public function __construct(private readonly string $name, private readonly array $operators)
    {
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string[]
     */
    public function getOperators(): array
    {
        return $this->operators;
    }
}
