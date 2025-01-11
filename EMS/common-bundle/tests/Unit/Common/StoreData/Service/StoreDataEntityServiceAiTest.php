<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Unit\Common\StoreData\Service;

use EMS\CommonBundle\Common\StoreData\Service\StoreDataEntityService;
use EMS\CommonBundle\Common\StoreData\StoreDataHelper;
use EMS\CommonBundle\Entity\StoreData;
use EMS\CommonBundle\Repository\StoreDataRepository;
use PHPUnit\Framework\TestCase;

class StoreDataEntityServiceAiTest extends TestCase
{
    private StoreDataRepository $repository;
    private StoreDataEntityService $service;

    #[\Override]
    protected function setUp(): void
    {
        $this->repository = $this->createMock(StoreDataRepository::class);
        $this->service = new StoreDataEntityService($this->repository);
    }

    public function testSave(): void
    {
        $dataHelper = new StoreDataHelper('key', ['data' => 'value']);
        $entity = new StoreData();

        $this->repository->expects($this->once())
            ->method('getByKey')
            ->with('key')
            ->willReturn(null);

        $this->repository->expects($this->once())
            ->method('update')
            ->with($this->isInstanceOf(StoreData::class));

        $this->service->save($dataHelper);
    }

    public function testRead(): void
    {
        $entity = new StoreData();
        $entity->setKey('key');
        $entity->setData(['data' => 'value']);

        $this->repository->expects($this->once())
            ->method('getByKey')
            ->with('key')
            ->willReturn($entity);

        $dataHelper = $this->service->read('key');
        $this->assertInstanceOf(StoreDataHelper::class, $dataHelper);
        $this->assertSame('key', $dataHelper->getKey());
        $this->assertSame(['data' => 'value'], $dataHelper->getData());
    }

    public function testDelete(): void
    {
        $entity = new StoreData();
        $entity->setKey('key');

        $this->repository->expects($this->once())
            ->method('getByKey')
            ->with('key')
            ->willReturn($entity);

        $this->repository->expects($this->once())
            ->method('delete')
            ->with($entity);

        $this->service->delete('key');
    }
}
