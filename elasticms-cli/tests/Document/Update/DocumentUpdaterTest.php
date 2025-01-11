<?php

declare(strict_types=1);

namespace App\CLI\Tests\Document\Update;

use App\CLI\Client\Data\Column\DataColumn;
use App\CLI\Client\Data\Column\TransformContext;
use App\CLI\Client\Data\Data;
use App\CLI\Client\Document\Update\DocumentUpdateConfig;
use App\CLI\Client\Document\Update\DocumentUpdater;
use EMS\CommonBundle\Contracts\CoreApi\CoreApiInterface;
use EMS\CommonBundle\Contracts\CoreApi\Endpoint\Data\DataInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Style\SymfonyStyle;

final class DocumentUpdaterTest extends TestCase
{
    private MockObject $coreApi;
    private MockObject $io;

    #[\Override]
    protected function setUp(): void
    {
        $this->coreApi = $this->createMock(CoreApiInterface::class);
        $this->io = $this->createMock(SymfonyStyle::class);
    }

    public function testExecuteColumnTransformers()
    {
        $data = new Data([['row1', 'test'], ['row2', 'test']]);

        $mockColumn = $this->createMock(DataColumn::class);
        $mockColumn
            ->expects($this->exactly(1))
            ->method('transform')
            ->with(
                $this->equalTo($data),
                $this->isInstanceOf(TransformContext::class)
            );

        $config = new DocumentUpdateConfig(['update' => ['contentType' => 'test', 'indexEmsId' => 0]]);
        $config->dataColumns = [$mockColumn];

        (new DocumentUpdater($data, $config, $this->coreApi, $this->io, false))->executeColumnTransformers();
    }

    public function testExecute()
    {
        $data = new Data([['ems:id1', 'title 111'], ['ems:id2', 'title 222']]);

        $this->io
            ->expects($this->once())
            ->method('createProgressBar')
            ->will($this->returnValue(new ProgressBar(new NullOutput(), 0)));

        $this->io->expects($this->never())->method('error');

        $dataEndpoint = $this->createMock(DataInterface::class);
        $dataEndpoint
            ->expects($this->exactly(2))
            ->method('save')
            ->withConsecutive(
                [$this->equalTo('id1'), $this->equalTo(['title' => 'title 111'])],
                [$this->equalTo('id2'), $this->equalTo(['title' => 'title 222'])]
            );

        $this->coreApi
            ->expects($this->once())->method('data')->willReturn($dataEndpoint);

        $config = new DocumentUpdateConfig([
            'update' => [
                'contentType' => 'test',
                'indexEmsId' => 0,
                'mapping' => [
                    ['field' => 'title', 'indexDataColumn' => 1],
                ],
            ], ]);

        (new DocumentUpdater($data, $config, $this->coreApi, $this->io, false))->execute();
    }

    public function testExecuteGrouped()
    {
        $data = new Data([['ems:id1', 'title 111'], ['ems:id1', 'title 111 bis'], ['ems:id2', 'title 222']]);

        $this->io
            ->expects($this->once())
            ->method('createProgressBar')
            ->will($this->returnValue(new ProgressBar(new NullOutput(), 0)));

        $this->io->expects($this->never())->method('error');

        $dataEndpoint = $this->createMock(DataInterface::class);
        $dataEndpoint
            ->expects($this->exactly(2))
            ->method('save')
            ->withConsecutive(
                [$this->equalTo('id1'), $this->equalTo(['collection' => [['title' => 'title 111'], ['title' => 'title 111 bis']]])],
                [$this->equalTo('id2'), $this->equalTo(['collection' => [['title' => 'title 222']]])]
            );

        $this->coreApi
            ->expects($this->once())->method('data')->willReturn($dataEndpoint);

        $config = new DocumentUpdateConfig([
            'update' => [
                'contentType' => 'test',
                'indexEmsId' => 0,
                'collectionField' => 'collection',
                'mapping' => [
                    ['field' => 'title', 'indexDataColumn' => 1],
                ],
            ], ]);

        (new DocumentUpdater($data, $config, $this->coreApi, $this->io, false))->execute();
    }
}
