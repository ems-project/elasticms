<?php

declare(strict_types=1);

namespace EMS\Helpers\Tests\Unit\Image;

use EMS\Helpers\Image\SmartCrop;
use PHPUnit\Framework\TestCase;

class SmartCropAiTest extends TestCase
{
    private \GdImage $image;
    private int $cropWidth = 100;
    private int $cropHeight = 100;

    #[\Override]
    protected function setUp(): void
    {
        $this->image = \imagecreatetruecolor(200, 200);
    }

    #[\Override]
    protected function tearDown(): void
    {
        \imagedestroy($this->image);
    }

    public function testConstructor()
    {
        $smartCrop = new SmartCrop($this->image, $this->cropWidth, $this->cropHeight);
        $this->assertInstanceOf(SmartCrop::class, $smartCrop);
    }

    public function testAnalyse()
    {
        $smartCrop = new SmartCrop($this->image, $this->cropWidth, $this->cropHeight);
        $result = $smartCrop->analyse();
        $this->assertIsArray($result);
        $this->assertArrayHasKey('topCrop', $result);
    }

    public function testCrop()
    {
        $smartCrop = new SmartCrop($this->image, $this->cropWidth, $this->cropHeight);
        $cropped = $smartCrop->crop(50, 50, $this->cropWidth, $this->cropHeight);
        $this->assertInstanceOf(SmartCrop::class, $cropped);
    }

    public function testGet()
    {
        $smartCrop = new SmartCrop($this->image, $this->cropWidth, $this->cropHeight);
        $resultImage = $smartCrop->get();
        $this->assertInstanceOf(\GdImage::class, $resultImage);
    }
}
