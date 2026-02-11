<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Twig;

use EMS\CommonBundle\Common\Converter;
use EMS\CommonBundle\Common\EMSLink;
use EMS\CommonBundle\Common\HttpCache\HttpCacheRuntime;
use EMS\CommonBundle\Helper\Text\Encoder;
use EMS\Helpers\Standard\Base64;
use EMS\Helpers\Standard\Color;
use EMS\Helpers\Standard\DateTime;
use EMS\Helpers\Standard\Number;
use EMS\Helpers\Standard\UuidGenerator;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class CommonExtension extends AbstractExtension
{
    #[\Override]
    public function getFunctions(): array
    {
        return [
            new TwigFunction('ems_html', [TextRuntime::class, 'emsHtml'], ['is_safe' => ['all']]),
            new TwigFunction('ems_http', [HttpClientRuntime::class, 'request']),
            new TwigFunction('ems_nested_search', [SearchRuntime::class, 'nestedSearch']),
            new TwigFunction('ems_analyze', [SearchRuntime::class, 'analyze']),
            new TwigFunction('ems_version', [InfoRuntime::class, 'version']),
            new TwigFunction('ems_uuid', UuidGenerator::random(...)),
            new TwigFunction('ems_store_read', [StoreDataRuntime::class, 'read']),
            new TwigFunction('ems_store_save', [StoreDataRuntime::class, 'save']),
            new TwigFunction('ems_store_delete', [StoreDataRuntime::class, 'delete']),
            new TwigFunction('ems_template_exists', [TemplateRuntime::class, 'templateExists']),
            new TwigFunction('ems_core', [CoreBridgeRuntime::class, 'build']),
            new TwigFunction('ems_flash', [RequestRuntime::class, 'flash']),
            new TwigFunction('ems_check_ip', [RequestRuntime::class, 'checkIp']),
            new TwigFunction('ems_clear_http_caches', [HttpCacheRuntime::class, 'clearCaches']),
        ];
    }

    #[\Override]
    public function getFilters(): array
    {
        return [
            new TwigFilter('ems_array_key', $this->arrayKey(...)),
            new TwigFilter('ems_file_exists', $this->fileExists(...)),
            new TwigFilter('ems_format_bytes', Number::formatBytes(...)),
            new TwigFilter('ems_ouuid', $this->getOuuid(...)),
            new TwigFilter('ems_locale_attr', [RequestRuntime::class, 'localeAttribute']),
            new TwigFilter('ems_html_encode', [TextRuntime::class, 'htmlEncode'], ['is_safe' => ['html']]),
            new TwigFilter('ems_html_decode', [TextRuntime::class, 'htmlDecode']),
            new TwigFilter('ems_anti_spam', [TextRuntime::class, 'htmlEncodePii'], ['is_safe' => ['html']]),
            new TwigFilter('ems_manifest', new ManifestRuntime()->manifest(...)),
            new TwigFilter('ems_json_menu_decode', [TextRuntime::class, 'jsonMenuDecode']),
            new TwigFilter('ems_json_menu_nested_decode', [TextRuntime::class, 'jsonMenuNestedDecode']),
            new TwigFilter('ems_json_decode', [TextRuntime::class, 'jsonDecode']),
            new TwigFilter('ems_ascii_folding', Encoder::asciiFolding(...)),
            new TwigFilter('ems_markdown', Encoder::markdownToHtml(...), ['is_safe' => ['html']]),
            new TwigFilter('ems_slug', [Encoder::class, 'slug']),
            new TwigFilter('ems_stringify', Converter::stringify(...)),
            new TwigFilter('ems_replace_regex', [TextRuntime::class, 'replaceRegex'], ['is_safe' => ['html']]),
            new TwigFilter('ems_dom_crawler', [TextRuntime::class, 'domCrawler']),
            new TwigFilter('ems_base64_encode', Base64::encode(...)),
            new TwigFilter('ems_base64_decode', Base64::decode(...)),
            new TwigFilter('ems_preg_match', Encoder::pregMatch(...)),
            new TwigFilter('ems_color', fn ($color) => new Color($color)),
            new TwigFilter('ems_link', EMSLink::fromText(...)),
            new TwigFilter('ems_valid_mail', [TextRuntime::class, 'isValidEmail']),
            new TwigFilter('ems_uuid', UuidGenerator::fromValue(...)),
            new TwigFilter('ems_date', DateTime::createFromFormat(...)),
            new TwigFilter('ems_int', intval(...)),
            new TwigFilter('ems_array_intersect', $this->arrayIntersect(...)),
            new TwigFilter('ems_array_merge_recursive', $this->arrayMergeRecursive(...)),
            new TwigFilter('ems_in_array', $this->inArray(...)),
            new TwigFilter('ems_md5', $this->md5(...)),
            new TwigFilter('ems_luma', $this->relativeLuminance(...)),
            new TwigFilter('ems_contrast_ratio', $this->contrastRatio(...)),
            new TwigFilter('ems_first_in_array', $this->firstInArray(...)),
        ];
    }

    public function fileExists(string $filename): bool
    {
        return \file_exists($filename);
    }

    /**
     * @param array<mixed> $array
     *
     * @return array<mixed>
     */
    public function arrayKey(array $array, string $key = 'key'): array
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

    public function getOuuid(string $emsLink): string
    {
        return EMSLink::fromText($emsLink)->getOuuid();
    }

    /**
     * @param array<mixed> $array1
     * @param array<mixed> $array2
     *
     * @return array<mixed>
     */
    public function arrayIntersect(array $array1, array $array2): array
    {
        return \array_intersect($array1, $array2);
    }

    /**
     * @param array<mixed> ...$arrays
     *
     * @return array<mixed>
     */
    public function arrayMergeRecursive(array ...$arrays): array
    {
        return \array_merge_recursive(...$arrays);
    }

    /**
     * @param array<mixed> $haystack
     */
    public function inArray(mixed $needle, array $haystack): bool
    {
        return false !== \array_search($needle, $haystack, true);
    }

    public function relativeLuminance(string $rgb): float
    {
        $color = new Color($rgb);

        return $color->relativeLuminance();
    }

    public function contrastRatio(string $c1, string $c2): float
    {
        $color1 = new Color($c1);
        $color2 = new Color($c2);

        return $color1->contrastRatio($color2);
    }

    public function md5(string $value): string
    {
        return \md5($value);
    }

    /**
     * @param array<mixed> $haystack
     */
    public function firstInArray(mixed $needle, array $haystack): bool
    {
        return 0 === \array_search($needle, $haystack);
    }
}
