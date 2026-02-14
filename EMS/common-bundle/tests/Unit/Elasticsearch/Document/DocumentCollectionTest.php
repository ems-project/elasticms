<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Unit\Elasticsearch\Document;

use EMS\CommonBundle\Elasticsearch\Document\DocumentCollection;
use EMS\CommonBundle\Elasticsearch\Document\DocumentInterface;
use EMS\CommonBundle\Elasticsearch\Response\ResponseInterface;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class DocumentCollectionTest extends TestCase
{
    private ResponseInterface $mockResponse;

    #[\Override]
    protected function setUp(): void
    {
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockResponse->method('getDocuments')
            ->willReturn([
                $this->createStub(DocumentInterface::class),
                $this->createStub(DocumentInterface::class),
            ]);
        $this->mockResponse = $mockResponse;
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testFromResponse(): void
    {
        $collection = DocumentCollection::fromResponse($this->mockResponse);
        $this->assertEquals(2, $collection->count());
    }

    #[AllowMockObjectsWithoutExpectations]
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
