<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Unit\Helper\Text;

use EMS\CommonBundle\Helper\Text\Encoder;
use PHPUnit\Framework\TestCase;

class EncoderTest extends TestCase
{
    private Encoder $encoder;

    protected function setUp(): void
    {
        $this->encoder = new Encoder();
        parent::setUp();
    }

    /**
     * format: [text, &#ascii;].
     */
    public function htmlProvider(): array
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

    /**
     * @dataProvider htmlProvider
     */
    public function testHtmlEncode(string $text, string $expected)
    {
        self::assertSame($expected, $this->encoder->htmlEncode($text));
    }

    /**
     * format: [text, &#ascii;].
     */
    public function piiProvider(): array
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

    /**
     * @dataProvider piiProvider
     */
    public function testHtmlEncodePii(string $text, string $expected)
    {
        self::assertSame($expected, $this->encoder->htmlEncodePii($text));
    }

    /**
     * format: [text, text;].
     */
    public function urlProvider(): array
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

    /**
     * @dataProvider urlProvider
     */
    public function testHtmlEncodeUrl(string $text, string $expected)
    {
        self::assertSame($expected, $this->encoder->encodeUrl($text));
    }

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
        self::assertSame('ueUEssSSaeoeAE', Encoder::asciiFolding('üÜßẞäöÄ', 'de'));
    }
}
