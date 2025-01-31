<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\PropertyAccess;

use EMS\CommonBundle\Helper\EmsFields;
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
     * @param mixed[]               $array
     * @param array<string, string> $replacers
     *
     * @return \Generator<string, mixed>
     */
    public function iterator(PropertyPath|string $propertyPath, array $array, array $replacers = [], string $basePath = ''): \Generator
    {
        $propertyPath = $this->getPropertyPath($propertyPath);
        $currentElement = $propertyPath->current();

        if ('*' === $currentElement->getName()) {
            foreach ($this->iterateOnAllChildren($propertyPath, $array, $replacers, $basePath) as $key => $value) {
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
            $realFieldName = empty($replacers) ? $field : \str_replace(\array_keys($replacers), \array_values($replacers), $field);
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
                foreach ($this->iterator($propertyPath, $decoded, $replacers, $path) as $key => $value) {
                    yield $key => $value;
                }
            }
        }
    }

    /**
     * @param  mixed[]                             $rawData
     * @return iterable<array<string, int|string>>
     */
    public function fileFields(array $rawData): iterable
    {
        yield from $this->returnFileFields($rawData);
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
     * @return iterable<array<string, int|string>>
     */
    private function returnFileFields(array $rawData, string $propertyPath = ''): iterable
    {
        foreach ($rawData as $key => $value) {
            if (\is_string($value) && u($value)->trim()->startsWith('{') && Json::isJson($value)) {
                $this->returnFileFields(Json::decode($value), \sprintf('%s[json:%s]', $propertyPath, $key));
                continue;
            }
            if (!\is_array($value)) {
                continue;
            }
            if (isset($value[EmsFields::CONTENT_FILE_HASH_FIELD]) || isset($value[EmsFields::CONTENT_FILE_HASH_FIELD_])) {
                yield \sprintf('%s[%s]', $propertyPath, $key) => $value;
                continue;
            }
            $this->returnFileFields($value, \sprintf('%s[%s]', $propertyPath, $key));
        }
    }
}
