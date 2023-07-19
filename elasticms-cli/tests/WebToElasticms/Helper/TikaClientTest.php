<?php

declare(strict_types=1);

namespace App\CLI\Tests\WebToElasticms\Helper;

use App\CLI\Helper\HtmlHelper;
use App\CLI\Helper\TikaClient;
use GuzzleHttp\Psr7\BufferStream;
use GuzzleHttp\Psr7\Stream;
use PHPUnit\Framework\TestCase;

class TikaClientTest extends TestCase
{
    public function testDocx(): void
    {
        $streamFrench = new BufferStream();
        $streamFrench->write('Bonjour, comment allez-vous?');
        $streamDutch = new BufferStream();
        $streamDutch->write('Hoi, hoe gaat het met je vanmorgen?');
        $client = new TikaClient();

        $this->assertEquals('fr', $client->meta($streamFrench, 'text/plain')->getLocale());
        $this->assertEquals('nl', $client->meta($streamDutch, 'text/plain')->getLocale());
    }

    public function testWordFile(): void
    {
        $client = new TikaClient();
        $bonjourDocx = new Stream(\fopen(\join(DIRECTORY_SEPARATOR, [__DIR__, 'resources', 'Bonjour.docx']), 'r'));
        $meta = $client->meta($bonjourDocx, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        $text = $client->text($bonjourDocx, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        $html = new HtmlHelper($client->html($bonjourDocx, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document')->getContent());
        $this->assertEquals('fr', $meta->getLocale());
        $this->assertEquals('Mathieu De Keyzer', $meta->getCreator());
        $this->assertEquals('Texte de test tika', $meta->getTitle());
        $this->assertEquals(new \DateTimeImmutable('2022-11-13T10:26:00Z'), $meta->getModified());
        $this->assertEquals(new \DateTimeImmutable('2022-11-13T10:02:00Z'), $meta->getCreated());
        $this->assertEquals('Bonjour lien vers google', $meta->getKeyword());
        $this->assertEquals('Elasticms', $meta->getPublisher());
        $this->assertEquals('[bookmark: _GoBack]Bonjour, comment allez-vous ? Voici un lien vers google. Bonne journée.', $text->getContent());
        $this->assertEquals('Bonjour, comment allez-vous ? Voici un lien vers google. Bonne journée.', $html->getText());
        $this->assertEquals(['https://www.google.com/'], $html->getLinks());
    }

    public function testPdfFile(): void
    {
        $client = new TikaClient();
        $bonjourPdf = new Stream(\fopen(\join(DIRECTORY_SEPARATOR, [__DIR__, 'resources', 'Bonjour.pdf']), 'r'));
        $meta = $client->meta($bonjourPdf, 'application/pdf');
        $text = $client->text($bonjourPdf, 'application/pdf');
        $html = new HtmlHelper($client->html($bonjourPdf, 'application/pdf')->getContent());
        $this->assertEquals('fr', $meta->getLocale());
        $this->assertEquals('Mathieu De Keyzer', $meta->getCreator());
        $this->assertEquals(new \DateTimeImmutable('2022-11-13T10:46:47Z'), $meta->getCreated());
        $this->assertEquals('Bonjour, comment allez-vous ? Voici un lien vers google. Bonne journée. https://www.google.com/', $text->getContent());
        $this->assertEquals('Bonjour, comment allez-vous ? Voici un lien vers google. Bonne journée. https://www.google.com/', $html->getText());
        $this->assertEquals(['https://www.google.com/'], $html->getLinks());
    }
}
