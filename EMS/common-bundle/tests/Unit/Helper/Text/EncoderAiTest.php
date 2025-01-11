<?php

namespace EMS\CommonBundle\Tests\Unit\Helper\Text;

use EMS\CommonBundle\Helper\Text\Encoder;
use PHPUnit\Framework\TestCase;

class EncoderAiTest extends TestCase
{
    private Encoder $encoder;

    #[\Override]
    protected function setUp(): void
    {
        $this->encoder = new Encoder();
    }

    public function testHtmlEncode(): void
    {
        $text = 'Test & Encode';
        $encoded = $this->encoder->htmlEncode($text);
        $this->assertEquals('&#84;&#101;&#115;&#116;&#32;&#38;&#32;&#69;&#110;&#99;&#111;&#100;&#101;', $encoded);
    }

    public function testHtmlDecode(): void
    {
        $text = 'Test &#38; Encode';
        $decoded = $this->encoder->htmlDecode($text, ENT_QUOTES, 'UTF-8');
        $this->assertEquals('Test & Encode', $decoded);
    }

    public function testMarkdownToHtml(): void
    {
        $markdown = "# Heading\n\nThis is a *markdown* text.";
        $html = $this->encoder->markdownToHtml($markdown);
        $this->assertEquals("<h1>Heading</h1>\n<p>This is a <em>markdown</em> text.</p>\n", $html);
    }

    public function testGetFontAwesomeFromMimeType(): void
    {
        $mimeType = 'application/pdf';
        $icon = Encoder::getFontAwesomeFromMimeType($mimeType, '5');
        $this->assertEquals('far fa-file-pdf', $icon);
    }

    public function testWebalizeForUsers(): void
    {
        $text = 'This is a Test';
        $webalized = $this->encoder->webalizeForUsers($text);
        $this->assertEquals('this-is-a-test', $webalized);
    }

    public function testAsciiFolding(): void
    {
        $text = 'Crème Brûlée';
        $folded = Encoder::asciiFolding($text);
        $this->assertEquals('Creme Brulee', $folded);
    }

    public function testHtmlEncodePiiEncodesPhoneNumbers(): void
    {
        $textWithPhone = 'Call me at <a href="tel:123-456-7890">123-456-7890</a>';
        $encoded = $this->encoder->htmlEncodePii($textWithPhone);

        $this->assertStringContainsString('&#34;&#116;&#101;&#108;&#58;&#49;&#50;&#51;&#45;&#52;&#53;&#54;&#45;&#55;&#56;&#57;&#48;&#34;', $encoded);
    }
}
