<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Unit\Common\Standard;

use EMS\CommonBundle\Common\Standard\Image;
use PHPUnit\Framework\TestCase;

class ImageAiTest extends TestCase
{
    private const string TEST_IMAGE_PATH = __DIR__.'/fixtures/image.png';

    public function testImageCreateFromString(): void
    {
        $imageContent = \file_get_contents(self::TEST_IMAGE_PATH);
        $image = Image::imageCreateFromString($imageContent);

        $this->assertTrue('object' == \gettype($image) && 'GdImage' == $image::class);
    }

    public function testImageResolution(): void
    {
        $resolution = Image::imageResolution(self::TEST_IMAGE_PATH);

        $this->assertIsArray($resolution);
        $this->assertCount(2, $resolution);
        $this->assertIsInt($resolution[0]);
        $this->assertIsInt($resolution[1]);
    }

    public function testImageSize(): void
    {
        $size = Image::imageSize(self::TEST_IMAGE_PATH);

        $this->assertIsArray($size);
        $this->assertArrayHasKey(0, $size);
        $this->assertArrayHasKey(1, $size);
        $this->assertArrayHasKey('mime', $size);
    }

    public function testImageCreateFromFilename(): void
    {
        $image = Image::imageCreateFromFilename(self::TEST_IMAGE_PATH);

        $this->assertTrue('object' == \gettype($image) && 'GdImage' == $image::class);
    }
}
