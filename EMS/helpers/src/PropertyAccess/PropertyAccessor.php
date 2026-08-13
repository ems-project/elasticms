<?php

declare(strict_types=1);

namespace EMS\Helpers\PropertyAccess;

use EMS\Helpers\Standard\Base64;
use EMS\Helpers\Standard\Json;

use function Symfony\Component\String\u;

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

        if ('**' === $currentElement->getName()) {
            if ($propertyPath->last()) {
                return $array;
            }

            $propertyPath->next();
            $targetId = $propertyPath->current()->getName();

            $found = $this->findRecursive($array, $targetId);
            if (null === $found) {
                return null;
            }

            if ($propertyPath->last()) {
                return $found;
            }

            $propertyPath->next();

            return $this->getValue($found, $propertyPath);
        }

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
     * @param array<mixed> $array
     *
     * @return ?array<mixed>
     */
    private function findRecursive(array $array, string $targetId): ?array
    {
        if (isset($array['id']) && (string) $array['id'] === $targetId) {
            return $array;
        }

        foreach ($array as $item) {
            if (!\is_array($item)) {
                continue;
            }

            if (isset($item['id']) && (string) $item['id'] === $targetId) {
                return $item;
            }

            $res = $this->findRecursive($item, $targetId);
            if ($res) {
                return $res;
            }
        }

        return null;
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

        if ('**' === $currentElement->getName()) {
            $propertyPath->next();
            $targetId = $propertyPath->current()->getName();

            if ($propertyPath->last()) {
                return;
            }

            $propertyPath->next();
            $this->setValueRecursive($array, $targetId, $propertyPath, $value);

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
     * @param array<mixed> $array
     */
    private function setValueRecursive(array &$array, string $targetId, PropertyPath $propertyPath, mixed $value): bool
    {
        if (isset($array['id']) && (string) $array['id'] === $targetId) {
            $this->setValue($array, $propertyPath, $value);

            return true;
        }

        foreach ($array as &$item) {
            if (!\is_array($item)) {
                continue;
            }

            if (isset($item['id']) && (string) $item['id'] === $targetId) {
                $this->setValue($item, $propertyPath, $value);

                return true;
            }

            if ($this->setValueRecursive($item, $targetId, $propertyPath, $value)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param mixed[]                 $array
     * @param array<string, string>   $replacers
     * @param array{id_as_key?: bool} $options
     *
     * @return \Generator<string, mixed>
     */
    public function iterator(PropertyPath|string $propertyPath, array $array, array $replacers = [], string $basePath = '', array $options = []): \Generator
    {
        $propertyPath = $this->getPropertyPath($propertyPath);
        $currentElement = $propertyPath->current();
        $currentName = $currentElement->getName();

        if (\in_array('id_key', $currentElement->getOperators())) {
            $options['id_as_key'] = true;
        }

        if ('*' === $currentName) {
            foreach ($this->iterateOnAllChildren($propertyPath, $array, $replacers, $basePath) as $key => $value) {
                yield $key => $value;
            }
        } elseif ('**' === $currentName) {
            $last = $propertyPath->last();
            $propertyPath->next();
            $index = $propertyPath->getIndex();

            yield from $this->iterateRecursive($propertyPath, $array, $replacers, $basePath, $last, $index, $options);

            return;
        }

        $last = $propertyPath->last();
        $propertyPath->next();

        $fields = \explode('|', $currentElement->getName());
        $operators = $currentElement->getOperatorsAsString();
        $index = $propertyPath->getIndex();
        foreach ($fields as $field) {
            $propertyPath->setIndex($index);
            $realFieldName = [] === $replacers ? $field : \str_replace(\array_keys($replacers), \array_values($replacers), $field);
            if (!isset($array[$realFieldName])) {
                continue;
            }
            $path = \sprintf('%s[%s%s]', $basePath, $operators, $field);
            $decoded = $this->decode($array[$realFieldName], $currentElement);
            if ($last) {
                yield $path => $decoded;
            } else {
                if (!\is_array($decoded)) {
                    throw new \RuntimeException('Unexpected non decoded array');
                }
                foreach ($this->iterator($propertyPath, $decoded, $replacers, $path, $options) as $key => $value) {
                    yield $key => $value;
                }
            }
        }
    }

    /**
     * @param  mixed[]                             $rawData
     * @param  string[]                            $attributeNames
     * @return iterable<array<string, int|string>>
     */
    public function fieldsWithAttributes(array $rawData, array $attributeNames, ?int $atLeast = null): iterable
    {
        if (null === $atLeast) {
            $atLeast = \count($attributeNames);
        }
        if ([] === $attributeNames) {
            throw new \RuntimeException("At least one attribute's name is required");
        }
        if ($atLeast < 1) {
            throw new \RuntimeException('The atLeast parameter must bigger than 0');
        }
        if ($atLeast > \count($attributeNames)) {
            throw new \RuntimeException("The atLeast can't be bigger than the number of looking attributes");
        }
        yield from $this->returnFieldsWithAttributes($rawData, $attributeNames, $atLeast);
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

    private function encode(mixed $value, PropertyPathElement $element): mixed
    {
        foreach (\array_reverse($element->getOperators()) as $operator) {
            $value = match ($operator) {
                'json' => Json::encode($value),
                'base64' => \is_string($value) ? Base64::encode($value) : throw new \RuntimeException('Only a string can be base64 encoded, array given'),
                'id_key' => \is_array($value) ? \array_values($value) : throw new \RuntimeException('Only an array can be use to retrieve the id property as array key'),
                default => throw new \RuntimeException(\sprintf('Operator %s not supported', $operator)),
            };
        }

        return $value;
    }

    private function decode(mixed $value, PropertyPathElement $element): mixed
    {
        foreach ($element->getOperators() as $operator) {
            $value = match ($operator) {
                'json' => \is_string($value) ? Json::decode($value) : throw new \RuntimeException('Only a string can be json decoded, array given'),
                'base64' => \is_string($value) ? Base64::decode($value) : throw new \RuntimeException('Only a string can be base64 decoded, array given'),
                'id_key' => \is_array($value) ? $this->idPropertyAsArrayKey($value) : throw new \RuntimeException('Only an array can be use to retrieve the id property as array key'),
                default => throw new \RuntimeException(\sprintf('Operator %s not supported', $operator)),
            };
        }

        return $value;
    }

    /**
     * @param mixed[]              $array
     * @param array<string,string> $replacers
     *
     * @return \Generator<string, mixed>
     */
    private function iterateOnAllChildren(PropertyPath $propertyPath, array $array, array $replacers, string $basePath): \Generator
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
                foreach ($this->iterator($propertyPath, $decoded, $replacers, $path) as $path => $childValue) {
                    yield $path => $childValue;
                }
            }
        }
    }

    /**
     * @param mixed[]                 $array
     * @param array<string,string>    $replacers
     * @param array{id_as_key?: bool} $options
     *
     * @return \Generator<string, mixed>
     */
    private function iterateRecursive(PropertyPath $propertyPath, array $array, array $replacers, string $basePath, bool $last, int $index, array $options = [], ?string $parentPath = null): \Generator
    {
        foreach ($array as $key => $item) {
            if (!\is_array($item)) {
                continue;
            }

            $useIdAsKey = $options['id_as_key'] ?? false;

            if ($useIdAsKey && isset($item['id']) && \is_scalar($item['id'])) {
                $currentPath = \sprintf('%s[**][%s]', $basePath, $item['id']);
            } else {
                $effectiveParent = $parentPath ?? $basePath;
                $currentPath = \sprintf('%s[%s]', $effectiveParent, $key);
            }

            $propertyPath->setIndex($index);

            if ($last) {
                yield $currentPath => $item;
            } else {
                yield from $this->iterator($propertyPath, $item, $replacers, $currentPath, $options);
            }

            $nextParentPath = ($useIdAsKey && isset($item['id']) && \is_scalar($item['id'])) ? $currentPath : $parentPath;

            yield from $this->iterateRecursive($propertyPath, $item, $replacers, $basePath, $last, $index, $options, $nextParentPath);
        }
    }

    /**
     * @param  mixed[] $array
     * @return mixed[]
     */
    private function idPropertyAsArrayKey(array $array): array
    {
        $withIdAskey = [];
        foreach ($array as $key => $value) {
            if (!isset($value['id'])) {
                throw new \RuntimeException(\sprintf('Property id is missing in item %d', $key));
            }
            $withIdAskey[(string) $value['id']] = $value;
        }

        return $withIdAskey;
    }

    /**
     * @param  mixed[]                             $rawData
     * @param  string[]                            $attributeNames
     * @return iterable<array<string, int|string>>
     */
    private function returnFieldsWithAttributes(array $rawData, array $attributeNames, int $atLeast, string $propertyPath = ''): iterable
    {
        foreach ($rawData as $key => $value) {
            if (\is_string($value) && u($value)->trim()->startsWith('{') && Json::isJson($value)) {
                $this->returnFieldsWithAttributes(Json::decode($value), $attributeNames, $atLeast, \sprintf('%s[json:%s]', $propertyPath, $key));
                continue;
            }
            if (!\is_array($value)) {
                continue;
            }
            if ($this->hasAtLeastAttribute($value, $attributeNames, $atLeast)) {
                yield \sprintf('%s[%s]', $propertyPath, $key) => $value;
                continue;
            }
            $this->returnFieldsWithAttributes($value, $attributeNames, $atLeast, \sprintf('%s[%s]', $propertyPath, $key));
        }
    }

    /**
     * @param mixed[]  $rawData
     * @param string[] $attributeNames
     */
    private function hasAtLeastAttribute(array $rawData, array $attributeNames, int $atLeast): bool
    {
        return \count(\array_intersect_key($rawData, \array_flip($attributeNames))) >= $atLeast;
    }
}
