<?php declare(strict_types=1);

namespace EMS\Helpers\ArrayHelper;

trait MapHelperTrait
{
    /**
     * @param array<mixed, mixed> $data
     *
     * @return array<mixed, mixed>
     */
    public static function map(array $data, callable $mapper): array
    {
        $result = [];

        foreach ($data as $property => &$value) {
            if (\is_array($value)) {
                $value = self::map($value, $mapper);
            }

            $result[$property] = $mapper($value, $property);
        }

        return $result;
    }
}