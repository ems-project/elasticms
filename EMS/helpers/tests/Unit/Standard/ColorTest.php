<?php

declare(strict_types=1);

namespace EMS\Helpers\Tests\Unit\Standard;

use EMS\Helpers\Standard\Color;
use PHPUnit\Framework\TestCase;

class ColorTest extends TestCase
{
    public function testBestContrast(): void
    {
        $this->assertSame('black', Color::fromString('red')->bestContrast('black')->html());
        $this->assertSame('white', Color::fromString('blue')->bestContrast('black')->html());
        $this->assertSame('white', Color::fromString('darkgreen')->bestContrast('black')->html());
        $this->assertSame('yellow', Color::fromString('green')->bestContrast('blue')->html());
        $this->assertSame('white', Color::fromString('green')->bestContrast('blue', 'red', 'white', 'black')->html());
        $this->assertSame('black', Color::fromString('white')->bestContrast('white')->html());
        $this->assertSame('blue', Color::fromString('white')->bestContrast('red', 'blue', 'green')->html());
        $this->assertSame('red', Color::fromString('black')->bestContrast('red', 'blue', 'green')->html());
        $this->assertSame('white', Color::fromString('black')->bestContrast(...Color::EMS_COLORS)->html());
        $this->assertSame('black', Color::fromString('white')->bestContrast(...Color::EMS_COLORS)->html());
        $this->assertSame('black', Color::fromString('red')->bestContrast(...Color::EMS_COLORS)->html());
        $this->assertSame('white', Color::fromString('green')->bestContrast(...Color::EMS_COLORS)->html());
        $this->assertSame('white', Color::fromString('blue')->bestContrast(...Color::EMS_COLORS)->html());
    }

    public function testAlpha(): void
    {
        $red = Color::fromString('red');
        $red->alpha = 128;
        $this->assertSame('#FF000080', $red->html());
        $this->assertSame('#FFFFFFFE', Color::fromString('FFFFFFFE')->html());
        $this->assertSame('white', Color::fromString('FFFFFFFF')->html());
        $this->assertSame('#FFFFFFFD', Color::fromString('FFFFFFFD')->html());
        $this->assertSame('white', Color::fromString('FFFFFF')->html());
        $this->assertSame('#FFFFFFAE', Color::fromString('FFFFFFAE')->html());
    }
}
