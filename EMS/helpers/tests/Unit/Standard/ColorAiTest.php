<?php

declare(strict_types=1);

namespace EMS\Helpers\Tests\Unit\Standard;

use EMS\Helpers\Standard\Color;
use PHPUnit\Framework\TestCase;

class ColorAiTest extends TestCase
{
    public function testConstructorWithEMSColor()
    {
        $color = new Color('ems-blue');
        $this->assertEquals(60, $color->red);
        $this->assertEquals(141, $color->green);
        $this->assertEquals(188, $color->blue);
    }

    public function testConstructorWithStandardHtmlColor()
    {
        $color = new Color('blue');
        $this->assertEquals(0, $color->red);
        $this->assertEquals(0, $color->green);
        $this->assertEquals(255, $color->blue);
    }

    public function testConstructorWithHexColor()
    {
        $color = new Color('#FF5733');
        $this->assertEquals(255, $color->red);
        $this->assertEquals(87, $color->green);
        $this->assertEquals(51, $color->blue);
    }

    public function testGetSetRed()
    {
        $color = new Color('#000000');
        $color->red = 123;
        $this->assertEquals(123, $color->red);
    }

    public function testGetSetGreen()
    {
        $color = new Color('#000000');
        $color->green = 123;
        $this->assertEquals(123, $color->green);
    }

    public function testGetSetBlue()
    {
        $color = new Color('#000000');
        $color->blue = 123;
        $this->assertEquals(123, $color->blue);
    }

    public function testGetSetAlpha()
    {
        $color = new Color('#000000');
        $color->alpha = 123;
        $this->assertEquals(123, $color->alpha);
    }

    public function testGetColorId()
    {
        $color = new Color('#000000');
        $image = \imagecreatetruecolor(100, 100);
        $colorId = $color->getColorId($image);
        $this->assertIsInt($colorId);
        \imagedestroy($image);
    }

    public function testRelativeLuminance()
    {
        $color = new Color('#FFFFFF');
        $this->assertEquals(1.0, $color->relativeLuminance(), '');
    }

    public function testContrastRatio()
    {
        $color1 = new Color('#FFFFFF');
        $color2 = new Color('#000000');
        $this->assertEquals(21, $color1->contrastRatio($color2), '');
    }

    public function testGetComplementary()
    {
        $color = new Color('#FFFFFF');
        $complementary = $color->getComplementary();
        $this->assertEquals(0, $complementary->red);
        $this->assertEquals(0, $complementary->green);
        $this->assertEquals(0, $complementary->blue);
    }

    public function testGetRGB()
    {
        $color = new Color('#FF5733');
        $this->assertEquals('#FF5733', $color->getRGB());
    }

    public function testGetRGBA()
    {
        $color = new Color('#FF5733');
        $color->alpha = 127;
        $this->assertEquals('#FF57337F', $color->getRGBA());
    }
}
