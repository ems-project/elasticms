<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Unit\Common\CoreApi\Search;

use EMS\CommonBundle\Common\CoreApi\Client;
use EMS\CommonBundle\Common\CoreApi\Result;
use EMS\CommonBundle\Common\CoreApi\Search\Scroll;
use EMS\CommonBundle\Elasticsearch\Document\Document;
use EMS\CommonBundle\Elasticsearch\Response\ResponseInterface;
use EMS\CommonBundle\Search\Search;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

class ScrollAiTest extends TestCase
{
    private Client $client;
    private Search $search;

    #[\Override]
    protected function setUp(): void
    {
        $this->client = $this->createMock(Client::class);
        $this->search = $this->createMock(Search::class);
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testCurrent(): void
    {
        $document = $this->createStub(Document::class);
        $response = $this->createConfiguredMock(ResponseInterface::class, [
            'getDocument' => $document,
            'getTotalDocuments' => 1,
        ]);

        $this->client
            ->method('post')
            ->willReturn($this->createConfiguredMock(Result::class, ['getData' => []]));

        $scroll = new Scroll($this->client, $this->search);
        $reflection = new \ReflectionClass($scroll);
        $property = $reflection->getProperty('currentResponse');
        $property->setValue($scroll, $response);

        $this->assertSame($document, $scroll->current());
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testKeyInvalidScroll(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid scroll');

        $scroll = new Scroll($this->client, $this->search);
        $scroll->key();
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testRewind(): void
    {
        $this->client
            ->expects($this->once())
            ->method('post')
            ->with('/api/search/init-scroll', [
                'search' => [],
                'expire-time' => '3m',
            ])
            ->willReturn($this->createConfiguredMock(Result::class, ['getData' => []]));

        $this->search
            ->method('toPayload')
            ->willReturn([]);

        $scroll = new Scroll($this->client, $this->search);
        $scroll->rewind();
    }
}
