<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Entity;

use EMS\CommonBundle\Entity\StoreData;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\UuidInterface;

class StoreDataAiTest extends TestCase
{
    private StoreData $storeData;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->storeData = new StoreData();
    }

    public function testGetId(): void
    {
        $this->assertInstanceOf(UuidInterface::class, $this->storeData->getId());
    }

    public function testKey(): void
    {
        $key = 'test-key';
        $this->storeData->setKey($key);

        $this->assertSame($key, $this->storeData->getKey());
    }

    public function testData(): void
    {
        $data = ['key1' => 'value1', 'key2' => 'value2'];
        $this->storeData->setData($data);

        $this->assertSame($data, $this->storeData->getData());
    }
}
