<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Elasticsearch\Document;

use EMS\Helpers\Standard\Json;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor as SymfonyPropertyAccessor;

class PropertyAccessor
{
    private SymfonyPropertyAccessor $propertyAccessor;

    private function __construct()
    {
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
    }

    public static function createPropertyAccessor(): PropertyAccessor
    {
        return new PropertyAccessor();
    }

    /**
     * @param mixed[] $array
     */
    public function getValue(array $array, string $propertyPath): mixed
    {
        if (false === $position = \strpos($propertyPath, '#')) {
            return $this->propertyAccessor->getValue($array, $propertyPath);
        }
        $path = \substr($propertyPath, 0, $position);
        $subPath = \substr($propertyPath, $position + 1);
        $json = $this->getValue($array, $path);
        if (null === $json) {
            return null;
        }
        $subArray = Json::decode($json);

        return $this->propertyAccessor->getValue($subArray, $subPath);
    }

    /**
     * @param mixed[] $array
     */
    public function setValue(array &$array, string $propertyPath, mixed $value): void
    {
        if (false === $position = \strpos($propertyPath, '#')) {
            $path = $propertyPath;
        } else {
            $path = \substr($propertyPath, 0, $position);
            $subPath = \substr($propertyPath, $position + 1);
            $subArray = Json::decode($this->getValue($array, $path) ?? '{}');
            $this->setValue($subArray, $subPath, $value);
            $value = Json::encode($subArray);
        }
        $this->propertyAccessor->setValue($array, $path, $value);
    }
}
