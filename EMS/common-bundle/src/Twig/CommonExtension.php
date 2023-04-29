<?php

namespace EMS\CommonBundle\Twig;

use EMS\CommonBundle\Common\Converter;
use EMS\CommonBundle\Common\EMSLink;
use EMS\CommonBundle\Common\Standard\Base64;
use EMS\CommonBundle\Helper\Text\Encoder;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class CommonExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('ems_asset_path', [AssetRuntime::class, 'assetPath'], ['is_safe' => ['html']]),
            new TwigFunction('ems_unzip', [AssetRuntime::class, 'unzip']),
            new TwigFunction('ems_json_file', [AssetRuntime::class, 'jsonFromFile']),
            new TwigFunction('ems_html', [TextRuntime::class, 'emsHtml'], ['is_safe' => ['all']]),
            new TwigFunction('ems_nested_search', [SearchRuntime::class, 'nestedSearch']),
            new TwigFunction('ems_image_info', [AssetRuntime::class, 'imageInfo']),
            new TwigFunction('ems_version', [InfoRuntime::class, 'version']),
            new TwigFunction('ems_store_read', [StoreRuntime::class, 'read']),
            new TwigFunction('ems_store_save', [StoreRuntime::class, 'save']),
        ];
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('array_key', $this->arrayKey(...)),
            new TwigFilter('ems_file_exists', $this->fileExists(...)),
            new TwigFilter('format_bytes', Converter::formatBytes(...)),
            new TwigFilter('emsch_ouuid', $this->getOuuid(...)),
            new TwigFilter('locale_attr', [RequestRuntime::class, 'localeAttribute']),
            new TwigFilter('ems_html_encode', [TextRuntime::class, 'htmlEncode'], ['is_safe' => ['html']]),
            new TwigFilter('ems_html_decode', [TextRuntime::class, 'htmlDecode']),
            new TwigFilter('ems_anti_spam', [TextRuntime::class, 'htmlEncodePii'], ['is_safe' => ['html']]),
            new TwigFilter('ems_manifest', (new ManifestRuntime())->manifest(...)),
            new TwigFilter('ems_json_menu_decode', [TextRuntime::class, 'jsonMenuDecode']),
            new TwigFilter('ems_json_menu_nested_decode', [TextRuntime::class, 'jsonMenuNestedDecode']),
            new TwigFilter('ems_json_decode', [TextRuntime::class, 'jsonDecode']),
            new TwigFilter('ems_webalize', [Encoder::class, 'webalizeForUsers']),
            new TwigFilter('ems_ascii_folding', [Encoder::class, 'asciiFolding']),
            new TwigFilter('ems_markdown', [Encoder::class, 'markdownToHtml'], ['is_safe' => ['html']]),
            new TwigFilter('ems_stringify', Converter::stringify(...)),
            new TwigFilter('ems_temp_file', [AssetRuntime::class, 'temporaryFile']),
            new TwigFilter('ems_asset_average_color', [AssetRuntime::class, 'assetAverageColor'], ['is_safe' => ['html']]),
            new TwigFilter('ems_replace_regex', [TextRuntime::class, 'replaceRegex'], ['is_safe' => ['html']]),
            new TwigFilter('ems_dom_crawler', [TextRuntime::class, 'domCrawler']),
            new TwigFilter('ems_base64_encode', Base64::encode(...)),
            new TwigFilter('ems_base64_decode', Base64::decode(...)),
            new TwigFilter('ems_hash', [AssetRuntime::class, 'hash']),
            new TwigFilter('ems_preg_match', [Encoder::class, 'pregMatch']),
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
}
