<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Unit\Elasticsearch\Document;

use EMS\CommonBundle\Common\EMSLink;
use EMS\CommonBundle\Elasticsearch\Document\Document;
use EMS\CommonBundle\Elasticsearch\Document\EMSSource;
use Elastica\Result;
use PHPUnit\Framework\TestCase;

final class DocumentAiTest extends TestCase
{
    private array $sampleDocumentData = [
        '_id' => '12345',
        '_index' => 'test_index',
        '_source' => [
            'title' => 'Test Title',
            '_contenttype' => 'test_content',
        ],
    ];

    public function testFromResult(): void
    {
        $result = $this->createMock(Result::class);
        $result->method('getHit')->willReturn($this->sampleDocumentData);

        $document = Document::fromResult($result);

        $this->assertEquals('12345', $document->getId());
    }

    public function testGetters(): void
    {
        $document = Document::fromArray($this->sampleDocumentData);

        $this->assertEquals('12345', $document->getId());
        $this->assertEquals('test_content', $document->getContentType());
        $this->assertEquals('test_content:12345', $document->getEmsId());
        $this->assertEquals('test_index', $document->getIndex());
        $this->assertEquals($this->sampleDocumentData, $document->getRaw());
        $this->assertNull($document->getHighlight());
    }

    public function testGetEmsLink(): void
    {
        $document = Document::fromArray($this->sampleDocumentData);
        $emsLink = $document->getEmsLink();

        $this->assertInstanceOf(EMSLink::class, $emsLink);
        $this->assertEquals('ems://object:test_content:12345', $emsLink->__toString());
    }

    public function testGetSource(): void
    {
        $document = Document::fromArray($this->sampleDocumentData);

        $this->assertEquals(['title' => 'Test Title', EMSSource::FIELD_CONTENT_TYPE => 'test_content'], $document->getSource());
        $this->assertEquals(['title' => 'Test Title'], $document->getSource(true));
    }

    public function testGetValue(): void
    {
        $document = Document::fromArray($this->sampleDocumentData);

        $this->assertEquals('Test Title', $document->getValue('[title]'));
        $this->assertNull($document->getValue('[description]'));
        $this->assertEquals('Default', $document->getValue('[description]', 'Default'));
    }

    public function testFieldPathToPropertyPath(): void
    {
        $this->assertEquals('[field][subfield]', Document::fieldPathToPropertyPath('field.subfield'));
        $this->assertEquals('[field][0]', Document::fieldPathToPropertyPath('field[0]'));
    }
}
