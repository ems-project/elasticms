<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Twig;

use EMS\CommonBundle\Helper\Text\Encoder;
use EMS\CommonBundle\Json\Decoder;
use EMS\CommonBundle\Twig\TextExtension;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TextExtensionTest extends TestCase
{
    private TextExtension $textExtension;

    #[\Override]
    protected function setUp(): void
    {
        $this->textExtension = new TextExtension(
            new Encoder(),
            new Decoder(),
            $this->createStub(ValidatorInterface::class),
            $this->createStub(LoggerInterface::class)
        );
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testReplaceInDom()
    {
        $source = <<<'HTML'
            <div class="ms-rtestate-read ms-rte-wpbox"><div class="ms-rtestate-notify  ms-rtestate-read 4e2af1bc-a4bc-4079-8549-f774e7ad0225" id="div_4e2af1bc-a4bc-4079-8549-f774e7ad0225" unselectable="on"><table style="width:100%" cellpadding="0" cellspacing="0"><tbody><tr><td id="MSOZoneCell_WebPartWPQ2" valign="top" class="s4-wpcell-plain "><table class="s4-wpTopTable " border="0" cellpadding="0" cellspacing="0" width="100%">
            	<tbody><tr>
            		<td><table border="0" cellpadding="0" cellspacing="0" width="100%">
            			<tbody><tr class="ms-WPHeader">
            				<td align="left" class="ms-wpTdSpace">&nbsp;</td><td title="Van meest recent naar oudst - Affiche une vue dynamique du contenu de votre site." id="WebPartTitleWPQ2" class="ms-WPHeaderTd"><h2 style="text-align:justify;" class="ms-WPTitle" id="Van_meest_recent_naar_oudst"><nobr>&nbsp;<span>Van meest recent naar oudst</span><span id="WebPartCaptionWPQ2"></span></nobr></h2></td><td class="ms-WPHeaderTdSelection"><span class="ms-WPHeaderTdSelSpan"><input type="checkbox" id="SelectionCbxWebPartWPQ2" class="ms-WPHeaderCbxHidden" title="Select or deselect Van meest recent naar oudst Web Part" onblur="this.className='ms-WPHeaderCbxHidden'" onfocus="this.className='ms-WPHeaderCbxVisible'" onkeyup="WpCbxKeyHandler(event);" onmouseup="WpCbxSelect(event); return false;" onclick="TrapMenuClick(event); return false;"></span></td><td align="left" class="ms-wpTdSpace">&nbsp;</td>
            			</tr>
            		</tbody></table></td>
            	</tr><tr>
            		<td class="" valign="top"><div webpartid="33cf0d11-13f2-4859-ad4c-b2085a8f6f77" webpartid2="4e2af1bc-a4bc-4079-8549-f774e7ad0225" haspers="false" id="WebPartWPQ2" width="100%" class="ms-WPBody ms-wpContentDivSpace " allowremove="false" allowdelete="false" style=""><div id="cbqwpctl00_m_g_4e2af1bc_a4bc_4079_8549_f774e7ad0225" class="cbq-layout-main"><ul class="dfwp-column dfwp-list" style="width:100%"><li class="dfwp-item"><div class="item link-item"><a href="https://www.example.com/SiteCollectionDocuments/tarief_opticiens_20230101.pdf" title="" onclick="">Tarieven van opticiens vanaf 1 januari 2023</a><div class="description">De veranderingen worden beschreven op pagina 1 van het document </div></div></li><li class="dfwp-item"><div class="item link-item"><a href="https://www.example.com/SiteCollectionDocuments/tarief_opticiens_20220601.pdf" title="" onclick="">Tarieven van opticiens vanaf 1 juni 2022</a><div class="description">De veranderingen worden beschreven op pagina 1 van het document </div></div></li><li class="dfwp-item"><div class="item link-item"><a href="https://www.example.com/SiteCollectionDocuments/tarief_opticiens_20220101.pdf" title="" onclick="">Tarieven van opticiens vanaf 1 januari 2022</a><div class="description">De veranderingen worden beschreven op pagina 1 van het document </div></div></li><li class="dfwp-item"><div class="item link-item"><a href="https://www.example.com/SiteCollectionDocuments/tarief_opticiens_20210601.pdf" title="" onclick="">Tarieven van opticiens vanaf 1 juni 2021</a><div class="description">De veranderingen worden beschreven op pagina 1 van het document</div></div></li><li class="dfwp-item"><div class="item link-item"><a href="https://www.example.com/SiteCollectionDocuments/tarief_opticiens_20210101.pdf" title="" onclick="">Tarieven van opticiens vanaf 1 januari 2021</a><div class="description">De veranderingen worden beschreven op pagina 1 van het document </div></div></li></ul></div></div></td>
            	</tr>
            </tbody></table></td></tr></tbody></table></div>
            <div id="vid_4e2af1bc-a4bc-4079-8549-f774e7ad0225" unselectable="on" style="display:none;"></div></div>
            HTML;

        $crawler = $this->textExtension->domCrawler($source);
        $webparts = $crawler->filter('div[webpartid]');

        for ($i = 0; $i < $webparts->count(); ++$i) {
            $webpart = $webparts->getNode($i);
            $webpartId = $webpart->attributes->getNamedItem('webpartid')->nodeValue;
            $this->assertEquals('33cf0d11-13f2-4859-ad4c-b2085a8f6f77', $webpartId);
            $parentName = null;
            $parent = $webpart;
            while ('div' !== $parentName) {
                $parent = $parent->parentNode;
                $parentName = $parent->localName;
            }
            $node = $parent->ownerDocument->createElement('a', $this->textExtension->domCrawler($parent)->filter('h2')->text());
            $node->setAttribute('href', 'ems://object:webpart:'.$webpartId);
            $parent->parentNode->replaceChild($node, $parent);
        }

        $this->assertEquals('<div class="ms-rtestate-read ms-rte-wpbox"><a href="ems://object:webpart:33cf0d11-13f2-4859-ad4c-b2085a8f6f77"> Van meest recent naar oudst</a>
<div id="vid_4e2af1bc-a4bc-4079-8549-f774e7ad0225" unselectable="on" style="display:none;"></div></div>', $crawler->filter('body')->html());
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testMarkdownToHtml(): void
    {
        $markdown = "# Heading\n\nThis is a *markdown* text.";
        $html = $this->textExtension->markdownToHtml($markdown);
        $this->assertEquals("<h1>Heading</h1>\n<p>This is a <em>markdown</em> text.</p>\n", $html);
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testAsciiFolding(): void
    {
        $text = 'Crème Brûlée';
        $folded = $this->textExtension->asciiFolding($text);
        $this->assertEquals('Creme Brulee', $folded);

        self::assertSame("l'iphone", $this->textExtension->asciiFolding("l'iphone"));
        self::assertSame("a_a-a a'a A", $this->textExtension->asciiFolding("a_a-a a'a A"));
        self::assertSame('aiea', $this->textExtension->asciiFolding('äîéà'));
        self::assertSame('ue UE ss SS ae oe AE OE', $this->textExtension->asciiFolding('ü Ü ß ẞ ä ö Ä Ö', 'de'));
        self::assertSame('u U ss SS a o A O', $this->textExtension->asciiFolding('ü Ü ß ẞ ä ö Ä Ö'));
        self::assertSame('Hello comment allez-vous ?', $this->textExtension->asciiFolding('Hello comment allez-vous ?', 'fr'));
    }

    /**
     * format: [text, text;].
     */
    public static function asciiProvider(): array
    {
        $a = ['―', '—', '–', '‒', '‹', '›', '′', '‘', '’', '‚', '‛', '″', '“', '”', '„', '‟', '«', '»', 'ß', 'ẞ', 'À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ', 'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ'];
        $b = ['-', '-', '-', '-', '<', '>', "'", "'", "'", ',', "'", '"', '"', '"', ',,', '"', '<<', '>>', 'ss', 'SS', 'A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'N', 'n', 'N', 'n', 'N', 'n', "'n", 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o'];

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
    #[AllowMockObjectsWithoutExpectations]
    public function testLegacyAsciiFolding(string $text, string $expected): void
    {
        self::assertSame($expected, $this->textExtension->asciiFolding($text));
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testUrlDecode(): void
    {
        $this->assertEquals(
            'hello world',
            $this->textExtension->urlDecode('hello+world')
        );

        $this->assertEquals(
            ['foo' => 'hello world', 'bar' => 'baz qux'],
            $this->textExtension->urlDecode('foo=hello+world&bar=baz+qux')
        );

        $this->assertEquals(
            ['foo' => 'bar', 'empty' => ''],
            $this->textExtension->urlDecode('foo=bar&empty')
        );

        $this->assertEquals(
            ['encoded key' => 'encoded value'],
            $this->textExtension->urlDecode('encoded+key=encoded+value')
        );
    }
}
