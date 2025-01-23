<?php

declare(strict_types=1);

namespace App\CLI\Tests\WebToElasticms\Helper;

use App\CLI\Helper\HtmlHelper;
use App\CLI\Helper\Tika\TikaHelper;
use EMS\CommonBundle\Helper\Url;
use GuzzleHttp\Psr7\BufferStream;
use GuzzleHttp\Psr7\Stream;
use PHPUnit\Framework\TestCase;

class TikaHelperTest extends TestCase
{
    public function testDocx(): void
    {
        $streamFrench = new BufferStream();
        $streamFrench->write('Bonjour, comment allez-vous?');
        $streamDutch = new BufferStream();
        $streamDutch->write('Hoi, hoe gaat het met je vanmorgen?');

        $helper = TikaHelper::initTikaServer($_SERVER['EMSCO_TIKA_SERVER']);

        $promiseFR = $helper->extract($streamFrench, 'text/plain');
        $promiseFR->startMeta();

        $promiseNL = $helper->extract($streamDutch, 'text/plain');
        $promiseNL->startMeta();

        $this->assertEquals('fr', $promiseFR->getMeta()->getLocale());
        $this->assertEquals('nl', $promiseNL->getMeta()->getLocale());
    }

    public function testWordFile(): void
    {
        $helper = TikaHelper::initTikaServer($_SERVER['EMSCO_TIKA_SERVER']);
        $bonjourDocx = new Stream(\fopen(\implode(DIRECTORY_SEPARATOR, [__DIR__, 'resources', 'Bonjour.docx']), 'r'));

        $promise = $helper->extract($bonjourDocx, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

        $promise->startMeta();
        $meta = $promise->getMeta();

        $promise->startText();
        $text = $promise->getText();

        $promise->startHtml();
        $content = $promise->getHtml();

        $html = new HtmlHelper($content, new Url('http://localhost'));
        $this->assertEquals('fr', $meta->getLocale());
        $this->assertEquals('Mathieu De Keyzer', $meta->getCreator());
        $this->assertEquals('Texte de test tika', $meta->getTitle());
        $this->assertEquals(new \DateTimeImmutable('2022-11-13T10:26:00Z'), $meta->getModified());
        $this->assertEquals(new \DateTimeImmutable('2022-11-13T10:02:00Z'), $meta->getCreated());
        $this->assertEquals('Bonjour lien vers google', $meta->getKeyword());
        $this->assertEquals('Elasticms', $meta->getPublisher());
        $this->assertEquals('[bookmark: _GoBack]Bonjour, comment allez-vous ? Voici un lien vers google. Bonne journée.', $text);
        $this->assertEquals('Bonjour, comment allez-vous ? Voici un lien vers google. Bonne journée.', $html->getText());
        $this->assertEquals(['https://www.google.com/' => 'google'], \iterator_to_array($html->getLinks()));
    }

    public function testPdfFile(): void
    {
        $helper = TikaHelper::initTikaServer($_SERVER['EMSCO_TIKA_SERVER']);
        $bonjourPdf = new Stream(\fopen(\implode(DIRECTORY_SEPARATOR, [__DIR__, 'resources', 'Bonjour.pdf']), 'r'));

        $promise = $helper->extract($bonjourPdf, 'application/pdf');

        $promise->startMeta();
        $meta = $promise->getMeta();

        $promise->startText();
        $text = $promise->getText();

        $promise->startHtml();
        $content = $promise->getHtml();

        $html = new HtmlHelper($content, new Url('http://localhost'));
        $this->assertEquals('fr', $meta->getLocale());
        $this->assertEquals('Mathieu De Keyzer', $meta->getCreator());
        $this->assertEquals(new \DateTimeImmutable('2022-11-13T10:46:47Z'), $meta->getCreated());
        $this->assertEquals('Bonjour, comment allez-vous ? Voici un lien vers google. Bonne journée. https://www.google.com/', $text);
        $this->assertEquals('Bonjour, comment allez-vous ? Voici un lien vers google. Bonne journée. https://www.google.com/', $html->getText());
        $this->assertEquals(['https://www.google.com/' => 'https://www.google.com/'], \iterator_to_array($html->getLinks()));
    }
}
