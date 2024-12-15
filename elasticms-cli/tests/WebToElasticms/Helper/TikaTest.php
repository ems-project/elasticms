<?php

declare(strict_types=1);

namespace App\CLI\Tests\WebToElasticms\Helper;

use App\CLI\Helper\HtmlHelper;
use App\CLI\Helper\Tika\TikaWrapper;
use EMS\CommonBundle\Helper\Url;
use GuzzleHttp\Psr7\BufferStream;
use GuzzleHttp\Psr7\Stream;
use PHPUnit\Framework\TestCase;

class TikaTest extends TestCase
{
    public function testLocales(): void
    {
        $streamFrench = new BufferStream();
        $streamFrench->write('Bonjour, comment allez-vous?');
        $streamDutch = new BufferStream();
        $streamDutch->write('Hoi, hoe gaat het met je vanmorgen?');
        $this->assertEquals('fr', TikaWrapper::getLanguage($streamFrench)->getOutput());
        $this->assertEquals('nl', TikaWrapper::getLanguage($streamDutch)->getOutput());
    }

    public function testWordFile(): void
    {
        $bonjourDocx = new Stream(\fopen(\join(DIRECTORY_SEPARATOR, [__DIR__, 'resources', 'Bonjour.docx']), 'r'));
        $this->assertEquals('fr', TikaWrapper::getLanguage($bonjourDocx)->getOutput());
        $this->assertEquals('Bonjour, comment allez-vous ? Voici un lien vers google. Bonne journée.', TikaWrapper::getText($bonjourDocx)->getOutput());
        $json = TikaWrapper::getJsonMetadata($bonjourDocx)->getJson();
        $this->assertEquals('Mathieu De Keyzer', $json['dc:creator'] ?? null);
        $this->assertEquals('Texte de test tika', $json['dc:title'] ?? null);
        $links = [];
        foreach ((new HtmlHelper(TikaWrapper::getHtml($bonjourDocx)->getOutput(), new Url('http://localhost')))->getLinks() as $link => $label) {
            $links[] = $link;
        }
        $this->assertEquals(['https://www.google.com/'], $links);
    }

    public function testPdfFile(): void
    {
        $bonjourPdf = new Stream(\fopen(\join(DIRECTORY_SEPARATOR, [__DIR__, 'resources', 'Bonjour.pdf']), 'r'));
        $this->assertEquals('fr', TikaWrapper::getLanguage($bonjourPdf)->getOutput());
        $this->assertEquals('Bonjour, comment allez-vous ? Voici un lien vers google. Bonne journée. https://www.google.com/', TikaWrapper::getText($bonjourPdf)->getOutput());
        $links = [];
        foreach ((new HtmlHelper(TikaWrapper::getHtml($bonjourPdf)->getOutput(), new Url('http://localhost')))->getLinks() as $link => $label) {
            $links[] = $link;
        }
        $this->assertEquals(['https://www.google.com/'], $links);
        $metaPromise = TikaWrapper::getJsonMetadata($bonjourPdf);
        $this->assertEquals('0', $metaPromise->getJson()['pdf:unmappedUnicodeCharsPerPage']);
    }
}
