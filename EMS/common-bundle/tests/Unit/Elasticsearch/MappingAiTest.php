<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Elasticsearch;

use EMS\CommonBundle\Elasticsearch\Client;
use EMS\CommonBundle\Elasticsearch\Mapping;
use PHPUnit\Framework\TestCase;

final class MappingAiTest extends TestCase
{
    private Client $client;
    private Mapping $mapping;

    #[\Override]
    protected function setUp(): void
    {
        $this->client = $this->createMock(Client::class);
        $this->mapping = new Mapping($this->client);
    }

    public function testDefaultMapping(): void
    {
        $expected = [
            '_contenttype' => ['type' => 'keyword'],
        ];
        $this->assertSame($expected, $this->mapping->defaultMapping());
    }

    public function testGetKeywordMapping(): void
    {
        $this->assertSame(['type' => 'keyword'], $this->mapping->getKeywordMapping());
    }

    public function testGetVersion(): void
    {
        $this->client->expects($this->once())->method('getVersion')->willReturn('7.10');
        $this->assertSame('7.10', $this->mapping->getVersion());
    }

    public function testGetNotIndexedStringMapping(): void
    {
        $this->assertSame(['type' => 'text', 'index' => false], $this->mapping->getNotIndexedStringMapping());
    }

    public function testGetDateTimeMapping(): void
    {
        $this->assertSame(['type' => 'date', 'format' => 'date_time_no_millis'], $this->mapping->getDateTimeMapping());
    }

    public function testGetIndexedStringMapping(): void
    {
        $this->assertSame(['type' => 'text', 'index' => true], $this->mapping->getIndexedStringMapping());
    }

    public function testGetLongMapping(): void
    {
        $this->assertSame(['type' => 'long'], $this->mapping->getLongMapping());
    }

    public function testGetFloatMapping(): void
    {
        $this->assertSame(['type' => 'float'], $this->mapping->getFloatMapping());
    }

    public function testGetLimitedKeywordMapping(): void
    {
        $this->assertSame(['type' => 'keyword', 'ignore_above' => 256], $this->mapping->getLimitedKeywordMapping());
    }

    public function testGetTextWithSubRawMapping(): void
    {
        $expected = [
            'type' => 'text',
            'fields' => [
                'raw' => [
                    'type' => 'keyword',
                ],
            ],
        ];
        $this->assertSame($expected, $this->mapping->getTextWithSubRawMapping());
    }

    public function testGetTextMapping(): void
    {
        $this->assertSame(['type' => 'text'], $this->mapping->getTextMapping());
    }
}
