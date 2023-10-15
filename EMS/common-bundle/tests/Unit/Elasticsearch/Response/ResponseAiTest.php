<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Unit\Elasticsearch\Response;

use Elastica\ResultSet;
use EMS\CommonBundle\Elasticsearch\Aggregation\Aggregation;
use EMS\CommonBundle\Elasticsearch\Document\Document;
use EMS\CommonBundle\Elasticsearch\Response\Response;
use PHPUnit\Framework\TestCase;

final class ResponseAiTest extends TestCase
{
    public function testFromArray(): void
    {
        $data = [
            'hits' => [
                'total' => [
                    'value' => 10,
                    'relation' => 'eq',
                ],
                'hits' => [
                    ['_index' => 'test_index', '_id' => '1', '_source' => ['_contenttype' => 'content-type']],
                    ['_index' => 'test_index', '_id' => '2', '_source' => ['_contenttype' => 'content-type']],
                ],
            ],
            'aggregations' => [
                'test_agg' => [],
            ],
            '_scroll_id' => 'scroll123',
        ];

        $response = Response::fromArray($data);

        $this->assertTrue($response->hasDocuments());
        $this->assertEquals(2, \iterator_count($response->getDocuments()));
        $this->assertInstanceOf(Document::class, $response->getDocument(0));
        $this->assertInstanceOf(Aggregation::class, $response->getAggregation('test_agg'));
        $this->assertEquals('scroll123', $response->getScrollId());
        $this->assertEquals(10, $response->getTotal());
        $this->assertEquals('10', $response->getFormattedTotal());
        $this->assertTrue($response->isAccurate());
    }

    public function testFromResultSet(): void
    {
        $resultSet = $this->createMock(ResultSet::class);
        $resultSet->method('getResponse')->willReturn($this->createConfiguredMock(\Elastica\Response::class, [
            'getData' => [
                'hits' => [
                    'total' => 5,
                    'hits' => [
                        ['_index' => 'test_index', '_id' => '3', '_source' => ['_contenttype' => 'content-type']],
                    ],
                ],
            ],
        ]));

        $response = Response::fromResultSet($resultSet);

        $this->assertTrue($response->hasDocuments());
        $this->assertEquals(1, \iterator_count($response->getDocuments()));
        $this->assertInstanceOf(Document::class, $response->getDocument(0));
        $this->assertEquals(5, $response->getTotal());
    }

    public function testGetAggregations(): void
    {
        $data = [
            'aggregations' => [
                'test_agg1' => [],
                'test_agg2' => [],
            ],
        ];

        $response = Response::fromArray($data);
        $aggregations = \iterator_to_array($response->getAggregations());

        $this->assertCount(2, $aggregations);
        $this->assertInstanceOf(Aggregation::class, $aggregations[0]);
        $this->assertInstanceOf(Aggregation::class, $aggregations[1]);
    }

    public function testGetDocumentCollection(): void
    {
        $data = [
            'hits' => [
                'hits' => [
                    ['_index' => 'test_index', '_id' => '1', '_source' => ['_contenttype' => 'content-type']],
                    ['_index' => 'test_index', '_id' => '2', '_source' => ['_contenttype' => 'content-type']],
                ],
            ],
        ];

        $response = Response::fromArray($data);
        $collection = $response->getDocumentCollection();

        $this->assertCount(2, $collection);
    }

    public function testGetTotalDocuments(): void
    {
        $data = [
            'hits' => [
                'hits' => [
                    ['_index' => 'test_index', '_id' => '1', '_source' => ['_contenttype' => 'content-type']],
                    ['_index' => 'test_index', '_id' => '2', '_source' => ['_contenttype' => 'content-type']],
                ],
            ],
        ];

        $response = Response::fromArray($data);
        $this->assertEquals(2, $response->getTotalDocuments());
    }

    public function testInaccurateTotal(): void
    {
        $data = [
            'hits' => [
                'total' => [
                    'value' => 10,
                    'relation' => 'gte',
                ],
            ],
        ];

        $response = Response::fromArray($data);
        $this->assertFalse($response->isAccurate());
        $this->assertEquals('≥10', $response->getFormattedTotal());
    }
}
