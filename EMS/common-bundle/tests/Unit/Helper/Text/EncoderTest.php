<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Unit\Helper\Text;

use EMS\CommonBundle\Helper\Text\Encoder;
use PHPUnit\Framework\Attributes\DataProvider;
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
            ['<a href="mailto:example@example.com">example@example.com</a>', \sprintf('<a href="mailto:%s">%s</a>', $email, $email)],
            ['href="tel:02/345.67.89"', 'href="tel:&#48;&#50;&#47;&#51;&#52;&#53;&#46;&#54;&#55;&#46;&#56;&#57;"'],
            ['<a href="tel:+3221234523">02/123.45.23</a>', '<a href="tel:&#43;&#51;&#50;&#50;&#49;&#50;&#51;&#52;&#53;&#50;&#51;">02/123.45.23</a>'],
            ['<span class="pii">example</span>', $example],
            ['<a href="tel:+3221234523">02/123.45.23</a> and another phone <a href="tel:+3221234523">02/123.45.23</a>', '<a href="tel:&#43;&#51;&#50;&#50;&#49;&#50;&#51;&#52;&#53;&#50;&#51;">02/123.45.23</a> and another phone <a href="tel:&#43;&#51;&#50;&#50;&#49;&#50;&#51;&#52;&#53;&#50;&#51;">02/123.45.23</a>'],
            ['<a href="/index.html">Homepage</a> and a phone <a href="tel:+3221234123">02/123.41.23</a> and another phone <a href="tel:+3221234523">02/123.45.23</a>.', '<a href="/index.html">Homepage</a> and a phone <a href="tel:&#43;&#51;&#50;&#50;&#49;&#50;&#51;&#52;&#49;&#50;&#51;">02/123.41.23</a> and another phone <a href="tel:&#43;&#51;&#50;&#50;&#49;&#50;&#51;&#52;&#53;&#50;&#51;">02/123.45.23</a>.'],
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

    public function testSlug(): void
    {
        self::assertSame('l-iphone', $this->encoder->slug('l\'iphone')->toString());
        self::assertSame('a-a-a-a-a-a', $this->encoder->slug('a_a-a a\'a A')->toString());
        self::assertSame('aiea', $this->encoder->slug('äîéà')->toString());
        self::assertSame('ueuessssaeoeae', $this->encoder->slug('üÜßẞäöÄ', 'de')->toString());
        self::assertSame('How/do/you/do', $this->encoder->slug('How do you do ? ', 'en', '/', false, true)->toString());
        self::assertSame('Wie/faehrst/du/deinen/grossen/LKW', $this->encoder->slug('Wie fährst du deinen großen LKW?', 'de', '/', false, true)->toString());
        self::assertSame('l-iphone.pdf', $this->encoder->slug('l\'Iphone.pDf', 'en', '-', true, true)->toString());
        self::assertSame('with-a-path-l-iphone.pdf', $this->encoder->slug('/With/A Path/l\'Iphone.pdf', 'en', '-', true, true)->toString());
        self::assertSame('l-iphone-pdf', $this->encoder->slug('l\'Iphone.pDf', 'en', '-', true)->toString());
        self::assertSame('with-a-path-l-iphone-pdf', $this->encoder->slug('/With/A Path/l\'Iphone.pdf', 'en', '-', true)->toString());
    }
}
