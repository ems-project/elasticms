<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Unit\Elasticsearch\Document;

use EMS\CommonBundle\Elasticsearch\Document\DocumentCollection;
use EMS\CommonBundle\Elasticsearch\Document\DocumentInterface;
use EMS\CommonBundle\Elasticsearch\Response\ResponseInterface;
use PHPUnit\Framework\TestCase;

class DocumentCollectionTest extends TestCase
{
    private ResponseInterface $mockResponse;

    #[\Override]
    protected function setUp(): void
    {
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockResponse->method('getDocuments')
            ->willReturn([
                $this->createMock(DocumentInterface::class),
                $this->createMock(DocumentInterface::class),
            ]);
        $this->mockResponse = $mockResponse;
    }

    public function testFromResponse(): void
    {
        $collection = DocumentCollection::fromResponse($this->mockResponse);
        $this->assertEquals(2, $collection->count());
    }

    public function testGetIterator(): void
    {
        $collection = DocumentCollection::fromResponse($this->mockResponse);

        $count = 0;
        foreach ($collection->getIterator() as $document) {
            self::assertInstanceOf(DocumentInterface::class, $document);
            ++$count;
        }

        $this->assertEquals(2, $count);
    }
}
