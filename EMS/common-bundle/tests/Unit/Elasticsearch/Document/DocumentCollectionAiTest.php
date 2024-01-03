<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Unit\Elasticsearch\Document;

use EMS\CommonBundle\Elasticsearch\Document\Document;
use EMS\CommonBundle\Elasticsearch\Document\DocumentCollection;
use EMS\CommonBundle\Elasticsearch\Response\Response;
use PHPUnit\Framework\TestCase;

final class DocumentCollectionAiTest extends TestCase
{
    private array $sampleResponseData = [
        'hits' => [
            'total' => 2,
            'hits' => [
                [
                    '_id' => '12345',
                    '_index' => 'test_index',
                    '_source' => [
                        'title' => 'Test Title 1',
                        '_contenttype' => 'test_content',
                    ],
                ],
                [
                    '_id' => '67890',
                    '_index' => 'test_index',
                    '_source' => [
                        'title' => 'Test Title 2',
                        '_contenttype' => 'test_content',
                    ],
                ],
            ],
        ],
    ];

    public function testFromResponse(): void
    {
        $response = Response::fromArray($this->sampleResponseData);
        $collection = DocumentCollection::fromResponse($response);

        $this->assertInstanceOf(DocumentCollection::class, $collection);
        $this->assertCount(2, $collection);
    }

    public function testGetIterator(): void
    {
        $response = Response::fromArray($this->sampleResponseData);
        $collection = DocumentCollection::fromResponse($response);

        $documents = [];
        foreach ($collection as $document) {
            $documents[] = $document;
        }

        $this->assertCount(2, $documents);
        $this->assertInstanceOf(Document::class, $documents[0]);
        $this->assertEquals('12345', $documents[0]->getId());
        $this->assertEquals('67890', $documents[1]->getId());
    }
}
