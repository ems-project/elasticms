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
        $this->assertEquals(60, $color->getRed());
        $this->assertEquals(141, $color->getGreen());
        $this->assertEquals(188, $color->getBlue());
    }

    public function testConstructorWithStandardHtmlColor()
    {
        $color = new Color('blue');
        $this->assertEquals(0, $color->getRed());
        $this->assertEquals(0, $color->getGreen());
        $this->assertEquals(255, $color->getBlue());
    }

    public function testConstructorWithHexColor()
    {
        $color = new Color('#FF5733');
        $this->assertEquals(255, $color->getRed());
        $this->assertEquals(87, $color->getGreen());
        $this->assertEquals(51, $color->getBlue());
    }

    public function testGetSetRed()
    {
        $color = new Color('#000000');
        $color->setRed(123);
        $this->assertEquals(123, $color->getRed());
    }

    public function testGetSetGreen()
    {
        $color = new Color('#000000');
        $color->setGreen(123);
        $this->assertEquals(123, $color->getGreen());
    }

    public function testGetSetBlue()
    {
        $color = new Color('#000000');
        $color->setBlue(123);
        $this->assertEquals(123, $color->getBlue());
    }

    public function testGetSetAlpha()
    {
        $color = new Color('#000000');
        $color->setAlpha(123);
        $this->assertEquals(123, $color->getAlpha());
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
        $this->assertEquals(1.0, $color->relativeLuminance(), '', 0.01);
    }

    public function testContrastRatio()
    {
        $color1 = new Color('#FFFFFF');
        $color2 = new Color('#000000');
        $this->assertEquals(21, $color1->contrastRatio($color2), '', 0.01);
    }

    public function testGetComplementary()
    {
        $color = new Color('#FFFFFF');
        $complementary = $color->getComplementary();
        $this->assertEquals(0, $complementary->getRed());
        $this->assertEquals(0, $complementary->getGreen());
        $this->assertEquals(0, $complementary->getBlue());
    }

    public function testGetRGB()
    {
        $color = new Color('#FF5733');
        $this->assertEquals('#FF5733', $color->getRGB());
    }

    public function testGetRGBA()
    {
        $color = new Color('#FF5733');
        $color->setAlpha(127);
        $this->assertEquals('#FF57337F', $color->getRGBA());
    }
}
