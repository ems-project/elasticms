<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Twig;

use EMS\CommonBundle\Storage\Processor\Processor;
use EMS\CommonBundle\Storage\StorageManager;
use EMS\CommonBundle\Twig\AssetRuntime;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AssetRuntimeTest extends TestCase
{
    private StorageManager $storageManager;
    private LoggerInterface $logger;
    private UrlGeneratorInterface $urlGenerator;
    private Processor $processor;

    #[\Override]
    public function setUp(): void
    {
        $this->storageManager = $this->createMock(StorageManager::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->processor = $this->createMock(Processor::class);
    }

    public function testImageInfoTempFileIsNull()
    {
        $assetRuntime = $this->getMockBuilder(AssetRuntime::class)
            ->setConstructorArgs([
                $this->storageManager,
                $this->logger,
                $this->urlGenerator,
                $this->processor,
                '',
            ])
            ->onlyMethods(['temporaryFile'])
            ->getMock();

        $hash = \sha1('testImageInfo');

        $assetRuntime
            ->expects($this->once())
            ->method('temporaryFile')
            ->with($hash)
            ->willReturn(null);

        $this->assertNull($assetRuntime->imageInfo($hash));
    }

    public function testImageInfoCanNotGetImageSize()
    {
        $assetRuntime = $this->getMockBuilder(AssetRuntime::class)
            ->setConstructorArgs([
                $this->storageManager,
                $this->logger,
                $this->urlGenerator,
                $this->processor,
                '',
            ])
            ->onlyMethods(['temporaryFile'])
            ->getMock();

        $hash = \sha1('testImageInfo');

        $assetRuntime
            ->expects($this->once())
            ->method('temporaryFile')
            ->with($hash)
            ->willReturn(__DIR__.'/ems.svg');

        $this->assertNull($assetRuntime->imageInfo($hash));
    }

    public function testImageInfo()
    {
        $assetRuntime = $this->getMockBuilder(AssetRuntime::class)
            ->setConstructorArgs([
                $this->storageManager,
                $this->logger,
                $this->urlGenerator,
                $this->processor,
                '',
            ])
            ->onlyMethods(['temporaryFile'])
            ->getMock();

        $hash = \sha1('testImageInfo');

        $assetRuntime
            ->expects($this->once())
            ->method('temporaryFile')
            ->with($hash)
            ->willReturn(__DIR__.'/ems.png');

        $expected = [
            'width' => 128,
            'height' => 128,
            'mimeType' => 'image/png',
            'extension' => 'png',
            'widthResolution' => 96,
            'heightResolution' => 96,
        ];

        $this->assertEquals($expected, $assetRuntime->imageInfo($hash));
    }

    public function testImageJpegInfo()
    {
        $assetRuntime = $this->getMockBuilder(AssetRuntime::class)
            ->setConstructorArgs([
                $this->storageManager,
                $this->logger,
                $this->urlGenerator,
                $this->processor,
                '',
            ])
            ->onlyMethods(['temporaryFile'])
            ->getMock();

        $hash = \sha1('testImageInfo');

        $assetRuntime
            ->expects($this->once())
            ->method('temporaryFile')
            ->with($hash)
            ->willReturn(__DIR__.'/test_350dpi.jpg');

        $expected = [
            'width' => 300,
            'height' => 300,
            'mimeType' => 'image/jpeg',
            'extension' => 'jpeg',
            'widthResolution' => 350,
            'heightResolution' => 350,
        ];

        $this->assertEquals($expected, $assetRuntime->imageInfo($hash));
    }
}
