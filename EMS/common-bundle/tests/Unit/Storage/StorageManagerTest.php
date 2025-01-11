<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Unit\Storage;

use EMS\CommonBundle\Helper\MimeTypeHelper;
use EMS\CommonBundle\Storage\Factory\FileSystemFactory;
use EMS\CommonBundle\Storage\Service\StorageInterface;
use EMS\CommonBundle\Storage\StorageManager;
use EMS\Helpers\File\TempDirectory;
use EMS\Helpers\File\TempFile;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Config\FileLocator;

class StorageManagerTest extends WebTestCase
{
    private const string BAR = 'bar';
    private const string FOO = 'foo';
    private StorageManager $storageManager;
    private TempFile $tempFile;
    private string $hash;
    private LoggerInterface $mockLogger;

    #[\Override]
    protected function setUp(): void
    {
        $this->mockLogger = $this->createMock(LoggerInterface::class);
        $this->storageManager = new StorageManager($this->mockLogger, new FileLocator(), [$this->getFsFactory()], 'sha1', [[
            'type' => 'fs',
            'path' => $this->getFsDir(),
        ], [
            'type' => 'fs',
            'path' => $this->getFsDir(),
        ], [
            'type' => 'fs',
            'path' => $this->getFsDir(),
            'usage' => StorageInterface::STORAGE_USAGE_ASSET_ATTRIBUTE,
        ]]);

        $this->tempFile = TempFile::create();
        \file_put_contents($this->tempFile->path, self::FOO.self::BAR);

        $this->hash = \sha1(self::FOO.self::BAR);
    }

    public function testHash(): void
    {
        $this->assertEquals('sha1', $this->storageManager->getHashAlgo());
        $this->assertEquals($this->hash, $this->storageManager->computeStringHash(self::FOO.self::BAR));
        $this->assertEquals($this->hash, $this->storageManager->computeFileHash($this->tempFile->path));
    }

    public function testHealthStatuses(): void
    {
        foreach ($this->storageManager->getHealthStatuses() as $status) {
            $this->assertTrue($status);
        }
    }

    public function testFoobarFileByChunkUpload(): void
    {
        $this->assertFalse($this->storageManager->head($this->hash));

        $size = \strlen(self::FOO.self::BAR);
        $this->assertGreaterThanOrEqual(1, $this->storageManager->initUploadFile($this->hash, $size, 'test.bin', 'application/bin', StorageInterface::STORAGE_USAGE_CACHE));
        $this->assertGreaterThanOrEqual(1, $this->storageManager->addChunk($this->hash, self::FOO, StorageInterface::STORAGE_USAGE_CACHE));
        $this->assertGreaterThanOrEqual(1, $this->storageManager->addChunk($this->hash, self::BAR, StorageInterface::STORAGE_USAGE_CACHE));
        $this->assertGreaterThanOrEqual(1, $this->storageManager->finalizeUpload($this->hash, $size, StorageInterface::STORAGE_USAGE_CACHE));

        $this->assertTrue($this->storageManager->head($this->hash));

        $ctx = \hash_init($this->storageManager->getHashAlgo());
        $stream = $this->storageManager->getStream($this->hash);
        $this->assertNotNull($stream);
        while (!$stream->eof()) {
            \hash_update($ctx, $stream->read(8192));
        }
        $computedHash = \hash_final($ctx);

        $this->assertEquals($this->hash, $computedHash);
        $this->assertEquals(2, \count($this->storageManager->headIn($this->hash)));

        $this->assertEquals(3, $this->storageManager->remove($this->hash));

        $this->assertFalse($this->storageManager->head($this->hash));
    }

    public function testFoobarFileBySingleUpload(): void
    {
        $temp = TempFile::create();
        $tempFile = $temp->path;
        if (!\is_string($tempFile)) {
            throw new \RuntimeException('Impossible to generate temporary filename');
        }
        $this->assertNotFalse(false !== $tempFile);
        $this->assertNotFalse(false !== \file_put_contents($tempFile, self::FOO.self::BAR));
        $this->assertEquals($this->hash, \hash_file($this->storageManager->getHashAlgo(), $tempFile));

        $hashAfterSave = $this->storageManager->saveFile($tempFile, StorageInterface::STORAGE_USAGE_ASSET);
        $this->assertEquals($this->hash, $hashAfterSave);
        $this->assertTrue($this->storageManager->head($this->hash));
        $this->assertEquals($this->hash, \hash($this->storageManager->getHashAlgo(), $this->storageManager->getContents($this->hash)));

        $this->assertEquals(\strlen(self::FOO.self::BAR), $this->storageManager->getSize($this->hash));
        $this->assertEquals(\base64_encode(self::FOO.self::BAR), $this->storageManager->getBase64($this->hash));

        $this->assertEquals(3, \count($this->storageManager->headIn($this->hash)));

        $this->assertEquals(3, $this->storageManager->remove($this->hash));
    }

    public function testSaveConfig(): void
    {
        $data = [
            self::FOO => self::BAR,
        ];

        $configHash = $this->storageManager->saveConfig($data);
        $this->assertEquals(\sha1(\json_encode($data)), $configHash);
        $this->assertEquals(1, \count($this->storageManager->headIn($configHash)));
        $this->assertEquals(3, $this->storageManager->remove($configHash));
    }

    public function testHotSynchronize(): void
    {
        $fsDirSource = $this->getFsDir();

        $storageManagerA = new StorageManager($this->mockLogger, new FileLocator(), [$this->getFsFactory()], 'sha1', [[
            'type' => 'fs',
            'path' => $fsDirSource,
        ]]);
        $hash = $storageManagerA->saveContents(self::FOO.self::BAR, 'foobar.txt', MimeTypeHelper::TEXT_PLAIN, StorageInterface::STORAGE_USAGE_ASSET);
        $this->assertEquals($this->hash, $hash);
        $this->assertEquals(1, \count($storageManagerA->headIn($hash)));

        $storageManagerB = new StorageManager($this->mockLogger, new FileLocator(), [$this->getFsFactory()], 'sha1', [[
            'type' => 'fs',
            'path' => $this->getFsDir(),
            'hot-synchronize-limit' => '5',
        ], [
            'type' => 'fs',
            'path' => $this->getFsDir(),
            'hot-synchronize-limit' => '1M',
        ], [
            'type' => 'fs',
            'path' => $fsDirSource,
        ], [
            'type' => 'fs',
            'path' => $fsDirSource,
            'hot-synchronize-limit' => '1M',
        ]]);

        $this->assertEquals(1, \count($storageManagerB->headIn($hash)));
        $this->assertEquals(self::FOO.self::BAR, $storageManagerB->getContents($hash));
        $this->assertEquals(2, \count($storageManagerB->headIn($hash)));
    }

    protected function getFsDir(): string
    {
        $fsDir = TempDirectory::create()->path;

        return $fsDir;
    }

    protected function getFsFactory(): FileSystemFactory
    {
        $fsFactory = new FileSystemFactory($this->mockLogger, $this->getFsDir());

        return $fsFactory;
    }
}
