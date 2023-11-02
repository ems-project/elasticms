<?php

declare(strict_types=1);

namespace EMS\Tests\CommonBundle\Unit\Elasticsearch\Aggregation;

use EMS\CommonBundle\Elasticsearch\Aggregation\Aggregation;
use EMS\CommonBundle\Elasticsearch\Aggregation\Bucket;
use PHPUnit\Framework\TestCase;

final class AggregationAiTest extends TestCase
{
    public function testAggregation(): void
    {
        $name = 'test_aggregation';
        $aggregationData = [
            'buckets' => [
                ['key' => 'key1', 'doc_count' => 5],
                ['key' => 'key2', 'doc_count' => 10],
            ],
            'doc_count' => 15,
        ];

        $aggregation = new Aggregation($name, $aggregationData);

        $this->assertEquals($name, $aggregation->getName());
        $this->assertEquals(15, $aggregation->getCount());
        $this->assertEquals($aggregationData, $aggregation->getRaw());

        $buckets = \iterator_to_array($aggregation->getBuckets());
        $this->assertCount(2, $buckets);
        $this->assertInstanceOf(Bucket::class, $buckets[0]);
        $this->assertEquals('key1', $buckets[0]->getKey());
        $this->assertEquals(5, $buckets[0]->getCount());

        $keys = $aggregation->getKeys();
        $this->assertEquals(['key1', 'key2'], $keys);
    }
}
