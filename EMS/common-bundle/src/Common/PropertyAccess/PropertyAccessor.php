<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\PropertyAccess;

use EMS\CommonBundle\Common\Standard\Base64;
use EMS\Helpers\Standard\Json;

class PropertyAccessor
{
    private static ?PropertyAccessor $instance = null;
    /** @var PropertyPath[] */
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
    public function getValue(array $array, PropertyPath|string $propertyPath): mixed
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
    public function setValue(array &$array, PropertyPath|string $propertyPath, mixed $value): void
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

    /**
     * @param mixed[] $array
     *
     * @return \Generator<string, mixed>
     */
    public function iterator(PropertyPath|string $propertyPath, array $array, string $basePath = ''): \Generator
    {
        $propertyPath = $this->getPropertyPath($propertyPath);
        $currentElement = $propertyPath->current();

        if ('*' === $currentElement->getName()) {
            foreach ($this->iterateOnAllChildren($propertyPath, $array, $basePath) as $key => $value) {
                yield $key => $value;
            }
        }

        $last = $propertyPath->last();
        $propertyPath->next();

        $fields = \explode('|', $currentElement->getName());
        $operators = $currentElement->getOperatorsAsString();
        $index = $propertyPath->getIndex();
        foreach ($fields as $field) {
            $propertyPath->setIndex($index);
            if (!isset($array[$field])) {
                continue;
            }
            $path = \sprintf('%s[%s%s]', $basePath, $operators, $field);
            $decoded = $this->decode($array[$field], $currentElement);
            if ($last) {
                yield $path => $decoded;
            } else {
                if (!\is_array($decoded)) {
                    throw new \RuntimeException('Unexpected non decoded array');
                }
                foreach ($this->iterator($propertyPath, $decoded, $path) as $key => $value) {
                    yield $key => $value;
                }
            }
        }
    }

    private function getPropertyPath(PropertyPath|string $propertyPath): PropertyPath
    {
        if ($propertyPath instanceof PropertyPath) {
            return $propertyPath;
        }

        if (isset($this->pathPropertiesCache[$propertyPath])) {
            $this->pathPropertiesCache[$propertyPath]->rewind();

            return $this->pathPropertiesCache[$propertyPath];
        }

        $propertyPathInstance = new PropertyPath($propertyPath);

        return $this->pathPropertiesCache[$propertyPath] = $propertyPathInstance;
    }

    /**
     * @param  mixed[]|string $value
     * @return string|mixed[]
     */
    private function encode(array|string $value, PropertyPathElement $element): string|array
    {
        foreach (\array_reverse($element->getOperators()) as $operator) {
            $value = match ($operator) {
                'json' => Json::encode($value),
                'base64' => \is_string($value) ? Base64::encode($value) : throw new \RuntimeException('Only a string can be base64 encoded, array given'),
                default => throw new \RuntimeException(\sprintf('Operator %s not supported', $operator))
            };
        }

        return $value;
    }

    /**
     * @param  mixed[]|string $value
     * @return string|mixed[]
     */
    private function decode(array|string $value, PropertyPathElement $element): string|array
    {
        foreach ($element->getOperators() as $operator) {
            $value = match ($operator) {
                'json' => \is_string($value) ? Json::decode($value) : throw new \RuntimeException('Only a string can be json decoded, array given'),
                'base64' => \is_string($value) ? Base64::decode($value) : throw new \RuntimeException('Only a string can be base64 decoded, array given'),
                default => throw new \RuntimeException(\sprintf('Operator %s not supported', $operator))
            };
        }

        return $value;
    }

    /**
     * @param mixed[] $array
     *
     * @return \Generator<string, mixed>
     */
    private function iterateOnAllChildren(PropertyPath $propertyPath, array $array, string $basePath): \Generator
    {
        $currentElement = $propertyPath->current();
        $last = $propertyPath->last();
        $propertyPath->next();
        $index = $propertyPath->getIndex();
        $operators = $currentElement->getOperatorsAsString();
        foreach ($array as $field => $value) {
            $path = \sprintf('%s[%s%s]', $basePath, $operators, $field);
            $decoded = $this->decode($value, $currentElement);
            $propertyPath->setIndex($index);
            if ($last) {
                yield $path => $decoded;
            } else {
                if (!\is_array($decoded)) {
                    throw new \RuntimeException('Unexpected non decoded array');
                }
                foreach ($this->iterator($propertyPath, $decoded, $path) as $path => $childValue) {
                    yield $path => $childValue;
                }
            }
        }
    }
}
