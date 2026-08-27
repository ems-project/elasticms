<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Unit\Common\StoreData\Factory;

use EMS\CommonBundle\Common\StoreData\Factory\StoreDataEntityFactory;
use EMS\CommonBundle\Common\StoreData\Service\StoreDataEntityService;
use EMS\CommonBundle\Repository\StoreDataRepository;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

class StoreDataEntityFactoryAiTest extends TestCase
{
    /**
     * @var Stub&StoreDataRepository
     */
    private Stub $repository;

    #[\Override]
    protected function setUp(): void
    {
        $this->repository = $this->createStub(StoreDataRepository::class);
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testGetType(): void
    {
        $factory = new StoreDataEntityFactory($this->repository);
        $this->assertSame(StoreDataEntityFactory::TYPE_DB, $factory->getType());
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testCreateService(): void
    {
        $factory = new StoreDataEntityFactory($this->repository);
        $service = $factory->createService(['type' => StoreDataEntityFactory::TYPE_DB]);

        $this->assertInstanceOf(StoreDataEntityService::class, $service);
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testCreateServiceWithInvalidType(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The option "type" with value "invalid" is invalid.');

        $factory = new StoreDataEntityFactory($this->repository);
        $factory->createService(['type' => 'invalid']);
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testCreateServiceWithoutType(): void
    {
        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage('The required option "type" is missing.');

        $factory = new StoreDataEntityFactory($this->repository);
        $factory->createService([]);
    }
}
