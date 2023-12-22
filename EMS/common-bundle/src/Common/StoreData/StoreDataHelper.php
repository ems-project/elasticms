<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\StoreData;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class StoreDataHelper
{
    private readonly PropertyAccessor $propertyAccessor;

    /**
     * @param mixed[] $data
     */
    public function __construct(private readonly string $key, private array $data = [])
    {
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getData(): mixed
    {
        return $this->data;
    }

    public function get(string $property): mixed
    {
        return $this->propertyAccessor->getValue($this->data, $property);
    }

    public function set(string $property, mixed $value): void
    {
        $this->propertyAccessor->setValue($this->data, $property, $value);
    }
}
