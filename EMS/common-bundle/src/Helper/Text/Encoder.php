<?php

namespace EMS\CommonBundle\Helper\Text;

use cebe\markdown\GithubMarkdown;
use EMS\CommonBundle\DependencyInjection\Configuration;
use Symfony\Component\String\AbstractUnicodeString;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\String\Slugger\SluggerInterface;

class Encoder
{
    private ?AsciiSlugger $slugger = null;

    /**
     * @param array<string, array<string, string>>|null $sluggerSymbolMap
     */
    public function __construct(private readonly string $webalizeRemovableRegex = Configuration::WEBALIZE_REMOVABLE_REGEX, private readonly string $webalizeDashableRegex = Configuration::WEBALIZE_DASHABLE_REGEX, private readonly ?array $sluggerSymbolMap = null)
    {
    }

    public function htmlEncode(string $text): string
    {
        return \mb_encode_numericentity(\html_entity_decode($text), [0x0, 0xFFFF, 0, 0xFFFF], 'UTF-8');
    }

    public function htmlDecode(string $text, int $flags, string $encoding): string
    {
        return \html_entity_decode($text, $flags, $encoding);
    }

    public function htmlEncodePii(string $text): string
    {
        return $this->encodePhone($this->encodeEmail($this->encodePiiClass($text)));
    }

    /**
     * Detect telephone information using the '"tel:xxx"' pattern
     * <a href="tel:02/123.45.23">02/123.45.23</a>.
     */
    private function encodePhone(string $text): string
    {
        $telRegex = '/(?P<tel>"tel:.*")/i';

        $encodedText = \preg_replace_callback($telRegex, fn ($match) => $this->htmlEncode($match['tel']), $text);

        if (null === $encodedText) {
            return $text;
        }

        return $encodedText;
    }

    /**
     * Detect url information using the '"http//host:proto/url/target"' pattern
     * <a href="http//host:proto/url/target">target</a>.
     */
    public function encodeUrl(string $text): string
    {
        $urlRegex = '/(?P<proto>([\w\d\-\.]+:)?)\/\/(?P<host>[\w\d\-\.]+(:[0-9]+)?)\/(?P<baseurl>([\w\d\-\._]+\/)*)(?P<target>[\w\d\-\._]+)/';

        $encodedText = \preg_replace_callback($urlRegex, fn ($matches) => \sprintf('<a href="%s//%s/%s%s">%s</a>', $matches['proto'], $matches['host'], $matches['baseurl'], $matches['target'], $matches['target']), $text);

        if (null === $encodedText) {
            return $text;
        }

        return $encodedText;
    }

    public function webalizeForUsers(string $text, string $locale = null): ?string
    {
        @\trigger_error('The webalizeForUsers method is deprecated, use the slug method', \E_USER_DEPRECATED);

        return static::webalize($text, $this->webalizeRemovableRegex, $this->webalizeDashableRegex, $locale);
    }

    public static function webalize(string $text, string $webalizeRemovableRegex = Configuration::WEBALIZE_REMOVABLE_REGEX, string $webalizeDashableRegex = Configuration::WEBALIZE_DASHABLE_REGEX, string $locale = null): string
    {
        @\trigger_error('The webalize method is deprecated, use the slug method', \E_USER_DEPRECATED);
        $clean = self::asciiFolding($text, $locale);
        $clean = \preg_replace($webalizeRemovableRegex, '', $clean) ?? '';
        $clean = \strtolower(\trim($clean, '-'));
        $clean = \preg_replace($webalizeDashableRegex, '-', $clean) ?? '';

        return $clean;
    }

    public function slug(string $text, string $locale = null, string $separator = '-', bool $lower = true): AbstractUnicodeString
    {
        $slugger = $this->getSlugger($locale ?? 'en');
        $slug = $slugger->slug($text, $separator, $locale);
        if ($lower) {
            $slug = $slug->lower();
        }

        return $slug;
    }

    public static function asciiFolding(string $text, string $locale = null): string
    {
        $a = ['―', '—', '–', '‒', '‹', '›', '′', '‵', '‘', '’', '‚', '‛', '″', '‴', '‶', '‷', '“', '”', '„', '‟', '«', '»', 'ß', 'ẞ', 'À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ', 'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ'];
        $b = ['-', '-', '-', '-', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', '"', '"', '"', '"', '"', '"', '"', '"', '"', '"', 'ss', 'SS', 'A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o'];

        if ('de' === $locale) {
            $a = \array_merge(['ü', 'Ü', 'ß', 'ẞ', 'ä', 'ö', 'Ä', 'Ö'], $a);
            $b = \array_merge(['ue', 'UE', 'ss', 'SS', 'ae', 'oe', 'AE', 'OE'], $b);
        }

        return \str_replace($a, $b, $text);
    }

    public static function markdownToHtml(string $markdown): string
    {
        static $parser;
        if (null === $parser) {
            $parser = new GithubMarkdown();
        }

        return $parser->parse($markdown);
    }

    /**
     * @return mixed[]
     */
    public static function pregMatch(string $subject, string $pattern, int $flags = PREG_SET_ORDER, int $offset = 0): array
    {
        $matches = [];
        if (false === \preg_match_all($pattern, $subject, $matches, $flags, $offset)) {
            return [];
        }

        return $matches;
    }

    /**
     * Detect email information using the 'x@x.x' pattern
     * <a href="mailto:david.meert@smals.be">david.meert@smals.be</a>.
     */
    private function encodeEmail(string $text): string
    {
        $emailRegex = '/(?P<email>[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3}))/i';

        $encodedText = \preg_replace_callback($emailRegex, fn ($match) => $this->htmlEncode($match['email']), $text);

        if (null === $encodedText) {
            return $text;
        }

        return $encodedText;
    }

    /**
     * Allow encoding other pii using a class "pii"
     * <a href="tel:02/123.45.23"><span class="pii">02/123.45.23</span></a>.
     *
     * The <span> element is consumed and is not kept in the end result.
     * example browser output: <a href="tel:02/123.45.23">02/123.45.23</a>
     *
     * If html tags are used inside a pii span, it will be double encoded and give unexpected results on the browser
     */
    private function encodePiiClass(string $text): string
    {
        $piiRegex = '/<span class="pii">(?P<pii>.*)<\/span>/m';

        $encodedText = \preg_replace_callback($piiRegex, fn ($match) => $this->htmlEncode($match['pii']), $text);

        if (null === $encodedText) {
            return $text;
        }

        return $encodedText;
    }

    /**
     * @return string
     */
    public static function getFontAwesomeFromMimeType(string $mimeType, string $version)
    {
        $versionIndex = 5;
        if (\version_compare($version, '5') < 0) {
            $versionIndex = 4;
        }

        // List of official MIME Types: http://www.iana.org/assignments/media-types/media-types.xhtml
        $icon_classes = [
            // Media
            'image' => [4 => 'fa fa-file-image-o', 5 => 'far fa-file-image'],
            'audio' => [4 => 'fa fa-file-audio-o', 5 => 'far fa-file-audio'],
            'video' => [4 => 'fa fa-file-video-o', 5 => 'far fa-file-video'],
            // Documents
            'application/pdf' => [4 => 'fa fa-file-pdf-o', 5 => 'far fa-file-pdf'],
            'application/msword' => [4 => 'fa fa-file-word-o', 5 => 'far fa-file-word'],
            'application/vnd.ms-word' => [4 => 'fa fa-file-word-o', 5 => 'far fa-file-word'],
            'application/vnd.oasis.opendocument.text' => [4 => 'fa fa-file-word-o', 5 => 'far fa-file-word'],
            'application/vnd.openxmlformats-officedocument.wordprocessingml' => [4 => 'fa fa-file-word-o', 5 => 'far fa-file-word'],
            'application/vnd.ms-excel' => [4 => 'fa fa-file-excel-o', 5 => 'far fa-file-excel'],
            'application/vnd.openxmlformats-officedocument.spreadsheetml' => [4 => 'fa fa-file-excel-o', 5 => 'far fa-file-excel'],
            'application/vnd.oasis.opendocument.spreadsheet' => [4 => 'fa fa-file-excel-o', 5 => 'far fa-file-excel'],
            'application/vnd.ms-powerpoint' => [4 => 'fa fa-file-powerpoint-o', 5 => 'far fa-file-powerpoint'],
            'application/vnd.openxmlformats-officedocument.presentationml' => [4 => 'fa fa-file-powerpoint-o', 5 => 'far fa-file-powerpoint'],
            'application/vnd.oasis.opendocument.presentation' => [4 => 'fa fa-file-powerpoint-o', 5 => 'far fa-file-powerpoint'],
            'text/plain' => [4 => 'fa fa-file-text-o', 5 => 'far fa-file-alt'],
            'text/html' => [4 => 'fa fa-file-code-o', 5 => 'far fa-file-code'],
            'application/json' => [4 => 'fa fa-file-code-o', 5 => 'far fa-file-code'],
            // Archives
            'application/gzip' => [4 => 'fa fa-file-archive-o', 5 => 'far fa-file-archive'],
            'application/zip' => [4 => 'fa fa-file-archive-o', 5 => 'far fa-file-archive'],
            'application/x-zip' => [4 => 'fa fa-file-archive-o', 5 => 'far fa-file-archive'],
        ];

        foreach ($icon_classes as $text => $icon) {
            if (\str_starts_with($mimeType, $text)) {
                return $icon[$versionIndex];
            }
        }

        $default = [4 => 'fa fa-file-o', 5 => 'far fa-file'];

        return $default[$versionIndex];
    }

    private function getSlugger(string $locale): SluggerInterface
    {
        if (null === $this->slugger) {
            $this->slugger = new AsciiSlugger($locale, $this->sluggerSymbolMap);
        } else {
            $this->slugger->setLocale($locale);
        }

        return $this->slugger;
    }
}
