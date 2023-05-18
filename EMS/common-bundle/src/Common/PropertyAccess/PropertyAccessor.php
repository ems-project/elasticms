<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\PropertyAccess;

use EMS\Helpers\Standard\Json;

class PropertyAccessor
{
    private static ?PropertyAccessor $instance = null;
    /** @var PathProperty[] */
    private array $pathPropertiesCache = [];

    private function __construct()
    {
    }

    public static function createPropertyAccessor(): PropertyAccessor
    {
        if (\is_null(self::$instance)) {
            self::$instance = new PropertyAccessor();
        }

        return self::$instance;
    }

    /**
     * @param mixed[] $array
     */
    public function getValue(array $array, PathProperty|string $propertyPath): mixed
    {
        $propertyPath = $this->getPropertyPath($propertyPath);
        $currentElement = $propertyPath->current();
        if (!isset($array[$currentElement->getName()])) {
            return null;
        }
        $decoded = $this->decode($array[$currentElement->getName()], $currentElement);
        if ($propertyPath->last()) {
            return $decoded;
        }
        if (\is_string($decoded)) {
            throw new \RuntimeException(\sprintf('Unexpected non decoded value: %s', $decoded));
        }
        $propertyPath->next();

        return $this->getValue($decoded, $propertyPath);
    }

    /**
     * @param mixed[] $array
     */
    public function setValue(array &$array, PathProperty|string $propertyPath, mixed $value): void
    {
        $propertyPath = $this->getPropertyPath($propertyPath);
        $currentElement = $propertyPath->current();
        if ($propertyPath->last()) {
            $array[$currentElement->getName()] = $this->encode($value, $currentElement);

            return;
        }
        if (!isset($array[$currentElement->getName()])) {
            $array[$currentElement->getName()] = [];
        } else {
            $array[$currentElement->getName()] = $this->decode($array[$currentElement->getName()], $currentElement);
        }
        $propertyPath->next();
        $this->setValue($array[$currentElement->getName()], $propertyPath, $value);
        $array[$currentElement->getName()] = $this->encode($array[$currentElement->getName()], $currentElement);
    }

    private function getPropertyPath(PathProperty|string $propertyPath): PathProperty
    {
        if ($propertyPath instanceof PathProperty) {
            return $propertyPath;
        }

        if (isset($this->pathPropertiesCache[$propertyPath])) {
            $this->pathPropertiesCache[$propertyPath]->rewind();

            return $this->pathPropertiesCache[$propertyPath];
        }

        $pathPropertyInstance = new PathProperty($propertyPath);

        return $this->pathPropertiesCache[$propertyPath] = $pathPropertyInstance;
    }

    /**
     * @param  mixed[]|string $value
     * @return string|mixed[]
     */
    private function encode(array|string $value, PathPropertyElement $element): string|array
    {
        foreach (\array_reverse($element->getOperators()) as $operator) {
            switch ($operator) {
                case 'json':
                    $value = Json::encode($value);
                    break;
                case 'base64':
                    if (!\is_string($value)) {
                        throw new \RuntimeException('Only a string can be base64 encoded, array found');
                    }
                    $value = \base64_encode($value);
                    break;
                default:
                    throw new \RuntimeException(\sprintf('Encoder %s not supported', $operator));
            }
        }

        return $value;
    }

    /**
     * @param  mixed[]|string $value
     * @return string|mixed[]
     */
    private function decode(array|string $value, PathPropertyElement $element): string|array
    {
        foreach ($element->getOperators() as $operator) {
            switch ($operator) {
                case 'json':
                    if (!\is_string($value)) {
                        throw new \RuntimeException('Only a string can be json decoded, array found');
                    }
                    $value = Json::decode($value);
                    break;
                case 'base64':
                    if (!\is_string($value)) {
                        throw new \RuntimeException('Only a string can be base64 decoded, array found');
                    }
                    $value = \base64_decode($value);
                    break;
                default:
                    throw new \RuntimeException(\sprintf('Encoder %s not supported', $operator));
            }
        }

        return $value;
    }
}
