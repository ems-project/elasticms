<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Unit\Elasticsearch\Document;

use EMS\CommonBundle\Elasticsearch\Document\EMSSource;
use PHPUnit\Framework\TestCase;

final class EMSSourceAiTest extends TestCase
{
    private array $sampleSourceData = [
        '_contenttype' => 'test_content',
        '_sha1' => 'sample_hash',
        '_finalized_by' => 'test_user',
        '_finalization_datetime' => '2023-10-06T12:00:00+0200',
        '_published_datetime' => '2023-10-06T13:00:00+0200',
    ];

    public function testConstructAndGetters(): void
    {
        $emsSource = new EMSSource($this->sampleSourceData);

        $this->assertEquals('test_content', $emsSource->getContentType());
        $this->assertEquals('sample_hash', $emsSource->getHash());
        $this->assertTrue($emsSource->hasFinalizedBy());
        $this->assertEquals('test_user', $emsSource->getFinalizedBy());
        $this->assertTrue($emsSource->hasFinalizationDateTime());
        $this->assertEquals('2023-10-06 12:00:00', $emsSource->getFinalizationDateTime()->format('Y-m-d H:i:s'));
        $this->assertTrue($emsSource->hasPublicationDateTime());
        $this->assertEquals('2023-10-06 13:00:00', $emsSource->getPublicationDateTime()->format('Y-m-d H:i:s'));
    }

    public function testGetWithDefault(): void
    {
        $emsSource = new EMSSource($this->sampleSourceData);
        $this->assertEquals('default_value', $emsSource->get('non_existent_field', 'default_value'));
    }

    public function testGetFinalizedByException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Finalized by missing');

        $sourceData = $this->sampleSourceData;
        unset($sourceData['_finalized_by']);

        $emsSource = new EMSSource($sourceData);
        $emsSource->getFinalizedBy();
    }

    public function testGetFinalizationDateTimeException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Finalization datetime by missing');

        $sourceData = $this->sampleSourceData;
        unset($sourceData['_finalization_datetime']);

        $emsSource = new EMSSource($sourceData);
        $emsSource->getFinalizationDateTime();
    }

    public function testGetPublicationDateTimeException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Finalization datetime by missing');

        $sourceData = $this->sampleSourceData;
        unset($sourceData['_published_datetime']);

        $emsSource = new EMSSource($sourceData);
        $emsSource->getPublicationDateTime();
    }

    public function testToArray(): void
    {
        $emsSource = new EMSSource($this->sampleSourceData);
        $this->assertEquals($this->sampleSourceData, $emsSource->toArray());
    }
}
