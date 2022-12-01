<?php

namespace EMS\CommonBundle\Helper;

class ArrayTool
{
    /**
     * Normalize and json encode an array in order to compute its hash.
     *
     * @param array<mixed> $array
     */
    public static function normalizeAndSerializeArray(array &$array, int $sort_flags = SORT_REGULAR, int $jsonEncodeOptions = 0): false|string
    {
        ArrayTool::normalizeArray($array, $sort_flags);

        return \json_encode($array, $jsonEncodeOptions);
    }

    /**
     * Normalize an array in order to compute its hash.
     *
     * @param array<mixed> $array
     */
    public static function normalizeArray(array &$array, int $sort_flags = SORT_REGULAR): void
    {
        \ksort($array, $sort_flags);

        foreach ($array as $index => &$arr) {
            if (\is_array($arr)) {
                ArrayTool::normalizeArray($arr, $sort_flags);
            }

            if (\is_array($array[$index]) && empty($array[$index])) {
                unset($array[$index]);
            }
        }
    }
}
