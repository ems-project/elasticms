<?php

declare(strict_types=1);

namespace App\Tests\WebToElasticms\Helper;

use App\Helper\HtmlHelper;
use App\Helper\TikaWrapper;
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
        $this->assertEquals('fr', TikaWrapper::getLocale($streamFrench, \sys_get_temp_dir())->getOutput());
        $this->assertEquals('nl', TikaWrapper::getLocale($streamDutch, \sys_get_temp_dir())->getOutput());
    }

    public function testWordFile(): void
    {
        $bonjourDocx = new Stream(\fopen(\join(DIRECTORY_SEPARATOR, [__DIR__, 'resources', 'Bonjour.docx']), 'r'));
        $this->assertEquals('fr', TikaWrapper::getLocale($bonjourDocx, \sys_get_temp_dir())->getOutput());
        $this->assertEquals('Bonjour, comment allez-vous ? Voici un lien vers google. Bonne journée.', TikaWrapper::getText($bonjourDocx, \sys_get_temp_dir())->getOutput());
        $json = TikaWrapper::getJsonMetadata($bonjourDocx, \sys_get_temp_dir())->getJson();
        $this->assertEquals('Mathieu De Keyzer', $json['dc:creator'] ?? null);
        $this->assertEquals('Texte de test tika', $json['dc:title'] ?? null);
        $this->assertEquals(['https://www.google.com/'], (new HtmlHelper(TikaWrapper::getHtml($bonjourDocx, \sys_get_temp_dir())->getOutput()))->getLinks());
    }

    public function testPdfFile(): void
    {
        $bonjourPdf = new Stream(\fopen(\join(DIRECTORY_SEPARATOR, [__DIR__, 'resources', 'Bonjour.pdf']), 'r'));
        $this->assertEquals('fr', TikaWrapper::getLocale($bonjourPdf, \sys_get_temp_dir())->getOutput());
        $this->assertEquals('Bonjour, comment allez-vous ? Voici un lien vers google. Bonne journée. https://www.google.com/', TikaWrapper::getText($bonjourPdf, \sys_get_temp_dir())->getOutput());
        $this->assertEquals(['https://www.google.com/'], (new HtmlHelper(TikaWrapper::getHtml($bonjourPdf, \sys_get_temp_dir())->getOutput()))->getLinks());
    }
}
