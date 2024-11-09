<?php

declare(strict_types=1);

namespace App\CLI\Tests\WebToElasticms\Helper;

use App\CLI\Helper\HtmlHelper;
use App\CLI\Helper\Tika\TikaJarPromise;
use EMS\CommonBundle\Helper\Url;
use GuzzleHttp\Psr7\Stream;
use PHPUnit\Framework\TestCase;

class TikaPromiseTest extends TestCase
{
    public function testWordFile(): void
    {
        $bonjourDocx = new Stream(\fopen(\join(DIRECTORY_SEPARATOR, [__DIR__, 'resources', 'Bonjour.docx']), 'r'));
        $promise = new TikaJarPromise($bonjourDocx);
        $meta = $promise->getMeta();
        $this->assertEquals('fr', $meta->getLocale());
        $this->assertEquals('Bonjour, comment allez-vous ? Voici un lien vers google. Bonne journée.', $promise->getText());
        $this->assertEquals('Mathieu De Keyzer', $meta->getCreator());
        $this->assertEquals('Texte de test tika', $meta->getTitle());
        $links = [];
        foreach ((new HtmlHelper($promise->getHtml(), new Url('http://localhost')))->getLinks() as $link => $label) {
            $links[] = $link;
        }
        $this->assertEquals(['https://www.google.com/'], $links);
    }

    public function testPdfFile(): void
    {
        $bonjourPdf = new Stream(\fopen(\join(DIRECTORY_SEPARATOR, [__DIR__, 'resources', 'Bonjour.pdf']), 'r'));
        $promise = new TikaJarPromise($bonjourPdf);
        $this->assertEquals('fr', $promise->getMeta()->getLocale());
        $this->assertEquals('Bonjour, comment allez-vous ? Voici un lien vers google. Bonne journée. https://www.google.com/', $promise->getText());
        $links = [];
        foreach ((new HtmlHelper($promise->getHtml(), new Url('http://localhost')))->getLinks() as $link => $label) {
            $links[] = $link;
        }
        $this->assertEquals(['https://www.google.com/'], $links);
    }
}
