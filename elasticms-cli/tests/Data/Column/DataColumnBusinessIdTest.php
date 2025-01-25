<?php

declare(strict_types=1);

namespace App\CLI\Tests\Data\Column;

use App\CLI\Client\Data\Column\DataColumnBusinessId;
use App\CLI\Client\Data\Column\TransformContext;
use App\CLI\Client\Data\Data;
use EMS\CommonBundle\Common\CoreApi\Endpoint\Search\Search;
use EMS\CommonBundle\Common\CoreApi\Search\Scroll;
use EMS\CommonBundle\Contracts\CoreApi\CoreApiInterface;
use EMS\CommonBundle\Elasticsearch\Document\Document;
use EMS\CommonBundle\Search\Search as SearchObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Style\SymfonyStyle;

final class DataColumnBusinessIdTest extends TestCase
{
    public function testInitialize(): void
    {
        $column = new DataColumnBusinessId([
            'type' => 'businessId',
            'contentType' => 'page',
            'field' => 'code',
            'index' => 0,
            'scrollSize' => 1000,
        ]);

        $this->assertSame('page', $column->contentType);
        $this->assertSame('code', $column->field);
        $this->assertSame(0, $column->columnIndex);
        $this->assertSame(1000, $column->scrollSize);
    }

    public function testRequiredOptions(): void
    {
        $this->expectExceptionMessage('The required options "contentType", "field", "index", "type" are missing.');
        new DataColumnBusinessId([]);
    }

    public function testTransform(): void
    {
        $column = new DataColumnBusinessId([
            'type' => 'businessId',
            'contentType' => 'page',
            'field' => 'code',
            'index' => 0,
            'scrollSize' => 1000,
        ]);

        $data = new Data([
            ['871267064', 'test',  'test'],
            ['438419214', 'test2',  'test2'],
        ]);

        $iterator = new \ArrayIterator([
            Document::fromArray([
                '_id' => 'docId1',
                '_source' => ['_contenttype' => 'page', 'code' => '871267064'],
                '_index' => 'test',
            ]),
            Document::fromArray([
                '_id' => 'docId2',
                '_source' => ['_contenttype' => 'page', 'code' => '438419214'],
                '_index' => 'test',
            ]),
            Document::fromArray([
                '_id' => 'docId3',
                '_source' => ['_contenttype' => 'page', 'code' => 'testX'],
                '_index' => 'test',
            ]),
        ]);

        $scroll = $this->createMock(Scroll::class);
        $scroll->expects($this->any())->method('current')->willReturnCallback(fn () => $iterator->current());
        $scroll->expects($this->any())->method('next')->willReturnCallback(fn () => $iterator->next());
        $scroll->expects($this->any())->method('rewind')->willReturnCallback(fn () => $iterator->rewind());
        $scroll->expects($this->any())->method('valid')->willReturnCallback(fn () => $iterator->valid());
        $scroll->expects($this->any())->method('key')->willReturnCallback(fn () => $iterator->key());

        $search = $this->createMock(Search::class);
        $search
            ->expects($this->once())
            ->method('scroll')
            ->with(
                $this->callback(function (SearchObject $search) {
                    $this->assertContains('code', $search->getSources());

                    return true;
                }),
                $this->equalTo(1000)
            )
            ->willReturn($scroll);

        $api = $this->createMock(CoreApiInterface::class);
        $api
            ->expects($this->once())
            ->method('search')
            ->willReturn($search);

        $io = $this->createMock(SymfonyStyle::class);
        $io
            ->expects($this->once())
            ->method('createProgressBar')
            ->willReturn(new ProgressBar(new NullOutput(), 0));

        $context = $this->getMockBuilder(TransformContext::class)
            ->setConstructorArgs([$api, $io])
            ->getMock();

        $column->transform($data, $context);
        $arrayData = \iterator_to_array($data);

        $this->assertSame('page:docId1', $arrayData[0][0]);
        $this->assertSame('page:docId2', $arrayData[1][0]);
    }
}
