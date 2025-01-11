<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Unit\Common\StoreData;

use EMS\CommonBundle\Common\StoreData\Factory\StoreDataFactoryInterface;
use EMS\CommonBundle\Common\StoreData\Service\StoreDataServiceInterface;
use EMS\CommonBundle\Common\StoreData\StoreDataHelper;
use EMS\CommonBundle\Common\StoreData\StoreDataManager;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class StoreDataManagerAiTest extends TestCase
{
    private LoggerInterface $logger;
    private StoreDataFactoryInterface $factory;

    #[\Override]
    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->factory = $this->createMock(StoreDataFactoryInterface::class);
    }

    public function testSave(): void
    {
        $service = $this->createMock(StoreDataServiceInterface::class);
        $service->expects($this->once())->method('save');

        $this->factory->method('getType')->willReturn('test_type');
        $this->factory->method('createService')->willReturn($service);

        $manager = new StoreDataManager($this->logger, [$this->factory], [['type' => 'test_type']]);
        $manager->save(new StoreDataHelper('test_key'));
    }

    public function testRead(): void
    {
        $service = $this->createMock(StoreDataServiceInterface::class);
        $service->method('read')->willReturn(new StoreDataHelper('test_key'));

        $this->factory->method('getType')->willReturn('test_type');
        $this->factory->method('createService')->willReturn($service);

        $manager = new StoreDataManager($this->logger, [$this->factory], [['type' => 'test_type']]);
        $result = $manager->read('test_key');

        $this->assertInstanceOf(StoreDataHelper::class, $result);
    }

    public function testDelete(): void
    {
        $service = $this->createMock(StoreDataServiceInterface::class);
        $service->expects($this->once())->method('delete');

        $this->factory->method('getType')->willReturn('test_type');
        $this->factory->method('createService')->willReturn($service);

        $manager = new StoreDataManager($this->logger, [$this->factory], [['type' => 'test_type']]);
        $manager->delete('test_key');
    }

    public function testMissingService(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No Store Data service is defined');

        $manager = new StoreDataManager($this->logger, [], []);
        $manager->save(new StoreDataHelper('test_key'));
    }

    public function testUnregisteredFactory(): void
    {
        $this->logger->expects($this->once())->method('warning')->with('Store data factory unregistered_type not registered');

        new StoreDataManager($this->logger, [$this->factory], [['type' => 'unregistered_type']]);
    }
}
