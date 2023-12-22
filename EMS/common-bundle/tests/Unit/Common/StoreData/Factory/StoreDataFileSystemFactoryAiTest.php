<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Unit\Common\StoreData\Factory;

use EMS\CommonBundle\Common\StoreData\Factory\StoreDataFileSystemFactory;
use EMS\CommonBundle\Common\StoreData\Service\StoreDataFileSystemService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

class StoreDataFileSystemFactoryAiTest extends TestCase
{
    public function testGetType(): void
    {
        $factory = new StoreDataFileSystemFactory();
        $this->assertSame('fs', $factory->getType());
    }

    public function testCreateService(): void
    {
        $factory = new StoreDataFileSystemFactory();
        $service = $factory->createService([
            'type' => 'fs',
            'path' => '/tmp',
        ]);

        $this->assertInstanceOf(StoreDataFileSystemService::class, $service);
    }

    public function testCreateServiceWithInvalidType(): void
    {
        $this->expectException(InvalidOptionsException::class);

        $factory = new StoreDataFileSystemFactory();
        $factory->createService([
            'type' => 'invalid_type',
            'path' => '/tmp',
        ]);
    }

    public function testCreateServiceWithoutPath(): void
    {
        $this->expectException(MissingOptionsException::class);

        $factory = new StoreDataFileSystemFactory();
        $factory->createService([
            'type' => 'fs',
        ]);
    }
}
