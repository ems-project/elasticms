<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Unit\Common\StoreData\Service;

use EMS\CommonBundle\Common\StoreData\Service\StoreDataFileSystemService;
use EMS\CommonBundle\Common\StoreData\StoreDataHelper;
use EMS\Helpers\File\TempDirectory;
use PHPUnit\Framework\TestCase;

class StoreDataFileSystemServiceAiTest extends TestCase
{
    private string $rootPath;
    private StoreDataFileSystemService $service;

    #[\Override]
    protected function setUp(): void
    {
        $this->rootPath = TempDirectory::create()->path;
        $this->service = new StoreDataFileSystemService($this->rootPath);
    }

    #[\Override]
    protected function tearDown(): void
    {
        \array_map('unlink', \glob($this->rootPath.DIRECTORY_SEPARATOR.'*'));
        \rmdir($this->rootPath);
    }

    public function testSaveAndRead(): void
    {
        $dataHelper = new StoreDataHelper('key', ['data' => 'value']);
        $this->service->save($dataHelper);

        $readDataHelper = $this->service->read('key');
        $this->assertInstanceOf(StoreDataHelper::class, $readDataHelper);
        $this->assertSame('key', $readDataHelper->getKey());
        $this->assertSame(['data' => 'value'], $readDataHelper->getData());
    }

    public function testDelete(): void
    {
        $dataHelper = new StoreDataHelper('key', ['data' => 'value']);
        $this->service->save($dataHelper);

        $this->service->delete('key');
        $this->assertNull($this->service->read('key'));
    }

    public function testInvalidKey(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The key invalid*key contains 1 invalid character(s): ^, *, ?, <, >, | ou :');

        $dataHelper = new StoreDataHelper('invalid*key', ['data' => 'value']);
        $this->service->save($dataHelper);
    }
}
