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
        $color->setAlphaGdValue(127);
        $this->assertEquals('#FF5733FF', $color->getRGBA());
    }
}
