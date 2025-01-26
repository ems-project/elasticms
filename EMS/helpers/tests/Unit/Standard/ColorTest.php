<?php

declare(strict_types=1);

namespace EMS\Helpers\Tests\Unit\Standard;

use EMS\Helpers\Standard\Color;
use PHPUnit\Framework\TestCase;

class ColorTest extends TestCase
{
    public function testBestContrast(): void
    {
        $this->assertEquals('black', Color::fromString('red')->bestContrast('black')->html());
        $this->assertEquals('white', Color::fromString('blue')->bestContrast('black')->html());
        $this->assertEquals('white', Color::fromString('darkgreen')->bestContrast('black')->html());
        $this->assertEquals('yellow', Color::fromString('green')->bestContrast('blue')->html());
        $this->assertEquals('white', Color::fromString('green')->bestContrast('blue', 'red', 'white', 'black')->html());
        $this->assertEquals('black', Color::fromString('white')->bestContrast('white')->html());
        $this->assertEquals('blue', Color::fromString('white')->bestContrast('red', 'blue', 'green')->html());
        $this->assertEquals('red', Color::fromString('black')->bestContrast('red', 'blue', 'green')->html());
        $this->assertEquals('white', Color::fromString('black')->bestContrast(...Color::EMS_COLORS)->html());
        $this->assertEquals('black', Color::fromString('white')->bestContrast(...Color::EMS_COLORS)->html());
        $this->assertEquals('black', Color::fromString('red')->bestContrast(...Color::EMS_COLORS)->html());
        $this->assertEquals('white', Color::fromString('green')->bestContrast(...Color::EMS_COLORS)->html());
        $this->assertEquals('white', Color::fromString('blue')->bestContrast(...Color::EMS_COLORS)->html());
    }
}
