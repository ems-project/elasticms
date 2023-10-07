<?php

namespace EMS\CommonBundle\Tests\Unit\Images;

use EMS\CommonBundle\Storage\Processor\Config;
use EMS\CommonBundle\Storage\Processor\Image;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ImageProcessorTest extends TestCase
{
    public function testResizeImage(): void
    {
        $config = $this->createMock(Config::class);
        $config->method('getConfigType')->willReturn('image');
        $config->method('getWidth')->willReturn(2880);
        $config->method('getHeight')->willReturn(1160);
        $config->method('getQuality')->willReturn(0);
        $config->method('getResize')->willReturn('fillArea');
        $logger = $this->createMock(LoggerInterface::class);
        $image = new Image($config, $logger);
        $generatedImage = $image->generate(__DIR__.DIRECTORY_SEPARATOR.'visuel_giant.jpg');
        \getimagesize($generatedImage);

        $this->assertSame([
            0 => 2880,
            1 => 1160,
            2 => 3,
            3 => 'width="2880" height="1160"',
            'bits' => 8,
            'mime' => 'image/png',
        ], \getimagesize($generatedImage));
    }
}
