<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Unit\Helper\Text;

use EMS\CommonBundle\Helper\Text\Encoder;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\IgnoreDeprecations;
use PHPUnit\Framework\TestCase;

class EncoderTest extends TestCase
{
    private Encoder $encoder;

    #[\Override]
    protected function setUp(): void
    {
        $this->encoder = new Encoder();
        parent::setUp();
    }

    /**
     * format: [text, &#ascii;].
     */
    public static function htmlProvider(): array
    {
        return [
            ['example', '&#101;&#120;&#97;&#109;&#112;&#108;&#101;'],
            ['@', '&#64;'],
            ['.', '&#46;'],
            ['example@example.com', '&#101;&#120;&#97;&#109;&#112;&#108;&#101;&#64;&#101;&#120;&#97;&#109;&#112;&#108;&#101;&#46;&#99;&#111;&#109;'],
            ['é', '&#233;'],
            ['<', '&#60;'],
        ];
    }

    #[DataProvider('htmlProvider')]
    public function testHtmlEncode(string $text, string $expected)
    {
        self::assertSame($expected, $this->encoder->htmlEncode($text));
    }

    /**
     * format: [text, &#ascii;].
     */
    public static function piiProvider(): array
    {
        $email = '&#101;&#120;&#97;&#109;&#112;&#108;&#101;&#64;&#101;&#120;&#97;&#109;&#112;&#108;&#101;&#46;&#99;&#111;&#109;';
        $example = '&#101;&#120;&#97;&#109;&#112;&#108;&#101;'; // example, no <span> tag included!

        return [
            ['example', 'example'],
            ['@', '@'],
            ['.', '.'],
            ['example@example.com', $email],
            ['é', 'é'],
            ['<', '<'],
            ['mailto:example@example.com', \sprintf('mailto:%s', $email)],
            ['"tel:02/345.67.89"', '&#34;&#116;&#101;&#108;&#58;&#48;&#50;&#47;&#51;&#52;&#53;&#46;&#54;&#55;&#46;&#56;&#57;&#34;'],
            ['<span class="pii">example</span>', $example],
        ];
    }

    #[DataProvider('piiProvider')]
    public function testHtmlEncodePii(string $text, string $expected)
    {
        self::assertSame($expected, $this->encoder->htmlEncodePii($text));
    }

    /**
     * format: [text, text;].
     */
    public static function urlProvider(): array
    {
        return [
            ['example', 'example'],
            ['See //host:80/demo/test.html', 'See <a href="//host:80/demo/test.html">test.html</a>'],
            ['//host/base/test', '<a href="//host/base/test">test</a>'],
            ['//host/base/more/complex/test', '<a href="//host/base/more/complex/test">test</a>'],
            ['//host/test', '<a href="//host/test">test</a>'],
            ['Before //host/base/test after', 'Before <a href="//host/base/test">test</a> after'],
            ['See http://host:80/demo/test.html', 'See <a href="http://host:80/demo/test.html">test.html</a>'],
            ['https://host/base/test', '<a href="https://host/base/test">test</a>'],
            ['ftp://host/base/more/complex/test', '<a href="ftp://host/base/more/complex/test">test</a>'],
            ['errr://host/test', '<a href="errr://host/test">test</a>'],
            ['errr//host/test', 'errr<a href="//host/test">test</a>'],
        ];
    }

    #[DataProvider('urlProvider')]
    public function testHtmlEncodeUrl(string $text, string $expected)
    {
        self::assertSame($expected, $this->encoder->encodeUrl($text));
    }

    #[IgnoreDeprecations]
    public function testWebalize(): void
    {
        self::assertSame('l-iphone', Encoder::webalize('l\'iphone'));
        self::assertSame('a_a-a-a-a-a', Encoder::webalize('a_a-a a\'a A'));
        self::assertSame('coucou-comment-vas-tu', Encoder::webalize('Coucou/Comment-vas tu?'));
    }

    public function testAsciiFolding(): void
    {
        self::assertSame('l\'iphone', Encoder::asciiFolding('l\'iphone'));
        self::assertSame('a_a-a a\'a A', Encoder::asciiFolding('a_a-a a\'a A'));
        self::assertSame('aiea', Encoder::asciiFolding('äîéà'));
        self::assertSame('ue UE ss SS ae oe AE OE', Encoder::asciiFolding('ü Ü ß ẞ ä ö Ä Ö', 'de'));
        self::assertSame('u U ss SS a o A O', Encoder::asciiFolding('ü Ü ß ẞ ä ö Ä Ö'));
        self::assertSame('Hello comment allez-vous ?', Encoder::asciiFolding('Hello comment allez-vous ?', 'fr'));
    }

    /**
     * format: [text, text;].
     */
    public static function asciiProvider(): array
    {
        $a = ['―', '—', '–', '‒', '‹', '›', '′', '‘', '’', '‚', '‛', '″', '“', '”', '„', '‟', '«', '»', 'ß', 'ẞ', 'À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ', 'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ'];
        $b = ['-', '-', '-', '-', '<', '>', '\'', '\'', '\'', ',', '\'', '"', '"', '"', ',,', '"', '<<', '>>', 'ss', 'SS', 'A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'N', 'n', 'N', 'n', 'N', 'n', '\'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o'];

        $cases = [];
        foreach ($a as $key => $value) {
            $cases[] = [
                $value,
                $b[$key] ?? null,
            ];
        }

        return $cases;
    }

    #[DataProvider('asciiProvider')]
    public function testLegacyAsciiFolding(string $text, string $expected): void
    {
        self::assertSame($expected, Encoder::asciiFolding($text));
    }

    public function testSlug(): void
    {
        self::assertSame('l-iphone', $this->encoder->slug('l\'iphone')->toString());
        self::assertSame('a-a-a-a-a-a', $this->encoder->slug('a_a-a a\'a A')->toString());
        self::assertSame('aiea', $this->encoder->slug('äîéà')->toString());
        self::assertSame('ueuessssaeoeae', $this->encoder->slug('üÜßẞäöÄ', 'de')->toString());
        self::assertSame('How/do/you/do', $this->encoder->slug('How do you do ? ', 'en', '/', false)->toString());
        self::assertSame('Wie/faehrst/du/deinen/grossen/LKW', $this->encoder->slug('Wie fährst du deinen großen LKW?', 'de', '/', false)->toString());
    }
}
