<?php

declare(strict_types=1);

namespace EMS\Tests\CommonBundle\Unit\Elasticsearch\Aggregation;

use EMS\CommonBundle\Elasticsearch\Aggregation\Bucket;
use PHPUnit\Framework\TestCase;

final class BucketAiTest extends TestCase
{
    public function testBucket(): void
    {
        $bucketData = [
            'key' => 'test_key',
            'doc_count' => 10,
            'sub_aggregation' => [
                'buckets' => [
                    ['key' => 'sub_key1', 'doc_count' => 5],
                    ['key' => 'sub_key2', 'doc_count' => 3],
                ],
            ],
        ];

        $bucket = new Bucket($bucketData);

        $this->assertEquals('test_key', $bucket->getKey());
        $this->assertEquals(10, $bucket->getCount());
        $this->assertEquals($bucketData, $bucket->getRaw());

        $subBuckets = \iterator_to_array($bucket->getSubBucket('sub_aggregation'));
        $this->assertCount(2, $subBuckets);
        $this->assertInstanceOf(Bucket::class, $subBuckets[0]);
        $this->assertEquals('sub_key1', $subBuckets[0]->getKey());
        $this->assertEquals(5, $subBuckets[0]->getCount());
    }
}
