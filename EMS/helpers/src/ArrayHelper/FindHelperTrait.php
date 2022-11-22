<?php

declare(strict_types=1);

namespace EMS\Helpers\ArrayHelper;

use EMS\Helpers\Standard\DateTime;
use EMS\Helpers\Standard\Type;

trait FindHelperTrait
{
    /**
     * @param array<mixed> $data
     */
    public static function findString(string $property, array $data): ?string
    {
        $get = self::find($property, $data)[0] ?? null;

        return $get ? Type::string($get) : null;
    }

    /**
     * @param array<mixed> $data
     */
    public static function findInteger(string $property, array $data): ?int
    {
        $get = self::find($property, $data)[0] ?? null;

        return $get ? Type::integer($get) : null;
    }

    /**
     * @param array<mixed> $data
     */
    public static function findDateTime(string $property, array $data, string $format = \DateTimeInterface::ATOM): ?\DateTimeInterface
    {
        $get = self::find($property, $data)[0] ?? null;

        return $get ? DateTime::createFromFormat($get, $format) : null;
    }

    /**
     * Recursively search provided $data array for $property.
     * Returns an array containing the value of the first hit.
     *
     * @param array<mixed> $data
     *
     * @return array<int, mixed>
     */
    public static function find(string $property, array $data): array
    {
        foreach ($data as $key => $value) {
            if ($key === $property) {
                return [$value];
            }
            if (\is_array($value)) {
                $subFind = self::find($property, $value);

                if (\count($subFind) > 0) {
                    return $subFind;
                }
            }
        }

        return [];
    }
}