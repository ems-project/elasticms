<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Mcp;

final class ElasticmsMcpJsonSchema
{
    /** @var array<string, true> */
    private const OBJECT_MAP_KEYS = [
        '$defs' => true,
        'definitions' => true,
        'dependentSchemas' => true,
        'patternProperties' => true,
        'properties' => true,
    ];

    /**
     * @param array<string, mixed> $schema
     *
     * @return array<string, mixed>
     */
    public static function normalize(array $schema): array
    {
        /** @var array<string, mixed> $normalized */
        $normalized = self::normalizeValue($schema);

        return $normalized;
    }

    private static function normalizeValue(mixed $value, ?string $key = null): mixed
    {
        if (!\is_array($value)) {
            return $value;
        }

        if ([] === $value && null !== $key && isset(self::OBJECT_MAP_KEYS[$key])) {
            return new \stdClass();
        }

        $normalized = [];
        foreach ($value as $childKey => $childValue) {
            $normalized[$childKey] = self::normalizeValue($childValue, \is_string($childKey) ? $childKey : null);
        }

        return $normalized;
    }
}
