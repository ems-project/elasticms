<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Twig;

use EMS\CommonBundle\Helper\Text\Encoder;
use EMS\CommonBundle\Json\Decoder;
use EMS\CommonBundle\Twig\TextRuntime;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TextRuntimeTest extends TestCase
{
    private LoggerInterface $logger;

    #[\Override]
    public function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->validator = $this->createMock(ValidatorInterface::class);
    }

    public function testReplaceInDom()
    {
        $textRuntime = new TextRuntime(
            new Encoder(),
            new Decoder(),
            $this->validator,
            $this->logger
        );

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

        $crawler = $textRuntime->domCrawler($source);
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
            $node = $parent->ownerDocument->createElement('a', $textRuntime->domCrawler($parent)->filter('h2')->text());
            $node->setAttribute('href', "ems://object:webpart:$webpartId");
            $parent->parentNode->replaceChild($node, $parent);
        }

        $this->assertEquals('<div class="ms-rtestate-read ms-rte-wpbox"><a href="ems://object:webpart:33cf0d11-13f2-4859-ad4c-b2085a8f6f77"> Van meest recent naar oudst</a>
<div id="vid_4e2af1bc-a4bc-4079-8549-f774e7ad0225" unselectable="on" style="display:none;"></div></div>', $crawler->filter('body')->html());
    }
}
