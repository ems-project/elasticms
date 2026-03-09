<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Twig;

use EMS\CommonBundle\Common\Converter;
use EMS\CommonBundle\Common\EMSLink;
use EMS\Helpers\Standard\Base64;
use EMS\Helpers\Standard\Color;
use EMS\Helpers\Standard\DateTime;
use EMS\Helpers\Standard\Number;
use EMS\Helpers\Standard\UuidGenerator;
use Ramsey\Uuid\UuidInterface;
use Twig\Attribute\AsTwigFilter;
use Twig\Attribute\AsTwigFunction;
use Twig\Extension\AbstractExtension;

class CommonExtension extends AbstractExtension
{
    #[AsTwigFilter('ems_base64_decode')]
    public static function base64Decode(string $value): string
    {
        return Base64::decode($value);
    }

    #[AsTwigFilter('ems_base64_encode')]
    public static function base64Encode(string $value): string
    {
        return Base64::encode($value);
    }

    #[AsTwigFilter('ems_color')]
    public static function color(string $color): Color
    {
        return new Color($color);
    }

    #[AsTwigFilter('ems_format_bytes')]
    public static function formatBytes(int $bytes, int $precision = 2): string
    {
        return Number::formatBytes($bytes, $precision);
    }

    #[AsTwigFilter('ems_link')]
    public static function link(string $text): EMSLink
    {
        return EMSLink::fromText($text);
    }

    #[AsTwigFilter('ems_stringify')]
    public static function stringify(mixed $var, string $defaultValue = ''): string
    {
        return Converter::stringify($var, $defaultValue);
    }

    /**
     * @param array<mixed> $array1
     * @param array<mixed> $array2
     *
     * @return array<mixed>
     */
    #[AsTwigFilter(name: 'ems_array_intersect')]
    public function arrayIntersect(array $array1, array $array2): array
    {
        return \array_intersect($array1, $array2);
    }

    /**
     * @param array<mixed> $array
     *
     * @return array<mixed>
     */
    #[AsTwigFilter(name: 'ems_array_key')]
    public function arrayKey(array $array, string $key): array
    {
        $out = [];

        foreach ($array as $id => $item) {
            if (isset($item[$key])) {
                $out[$item[$key]] = $item;
            } else {
                $out[$id] = $item;
            }
        }

        return $out;
    }

    /**
     * @param array<mixed> ...$arrays
     *
     * @return array<mixed>
     */
    #[AsTwigFilter(name: 'ems_array_merge_recursive')]
    public function arrayMergeRecursive(array ...$arrays): array
    {
        return \array_merge_recursive(...$arrays);
    }

    #[AsTwigFilter(name: 'ems_date')]
    public function castDate(string $time, string $format): \DateTimeImmutable
    {
        return DateTime::createFromFormat($time, $format);
    }

    #[AsTwigFilter(name: 'ems_int')]
    public function castInt(mixed $value): int
    {
        return (int) $value;
    }

    #[AsTwigFilter(name: 'ems_contrast_ratio')]
    public function contrastRatio(string $c1, string $c2): float
    {
        $color1 = new Color($c1);
        $color2 = new Color($c2);

        return $color1->contrastRatio($color2);
    }

    #[AsTwigFilter(name: 'ems_file_exists')]
    public function fileExists(string $filename): bool
    {
        return \file_exists($filename);
    }

    /**
     * @param array<mixed> $haystack
     */
    #[AsTwigFilter(name: 'ems_first_in_array')]
    public function firstInArray(mixed $needle, array $haystack): bool
    {
        return 0 === \array_search($needle, $haystack, true);
    }

    #[AsTwigFilter(name: 'ems_ouuid')]
    public function getOuuid(string $emsLink): string
    {
        return EMSLink::fromText($emsLink)->getOuuid();
    }

    #[AsTwigFunction(name: 'ems_uuid')]
    public function getUuid(): UuidInterface
    {
        return UuidGenerator::random();
    }

    #[AsTwigFilter(name: 'ems_uuid')]
    public function getUuidFromValue(string $value): UuidInterface
    {
        return UuidGenerator::fromValue($value);
    }

    /**
     * @param array<mixed> $haystack
     */
    #[AsTwigFilter(name: 'ems_in_array')]
    public function inArray(mixed $needle, array $haystack): bool
    {
        return \in_array($needle, $haystack, true);
    }

    #[AsTwigFilter(name: 'ems_md5')]
    public function md5(string $value): string
    {
        return \md5($value);
    }

    #[AsTwigFilter(name: 'ems_luma')]
    public function relativeLuminance(string $rgb): float
    {
        $color = new Color($rgb);

        return $color->relativeLuminance();
    }
}
