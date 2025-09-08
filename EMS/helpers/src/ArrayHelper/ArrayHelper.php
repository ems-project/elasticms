<?php

declare(strict_types=1);

namespace EMS\Helpers\ArrayHelper;

class ArrayHelper
{
    use FindHelperTrait;
    use MapHelperTrait;

    /**
     * @param mixed|mixed[] $array1
     * @param mixed|mixed[] $array2
     * @return bool
     */
    public static function arrays_are_equal_recursive($array1, $array2): bool
    {
        if (!\is_array($array1) || !\is_array($array2)) {
            return $array1 === $array2;
        }

        if (\count($array1) !== \count($array2)) {
            return false;
        }

        foreach ($array1 as $key => $value) {
            if (!\array_key_exists($key, $array2)) {
                return false;
            }
            if (!self::arrays_are_equal_recursive($value, $array2[$key])) {
                return false;
            }
        }

        return true;
    }
}
