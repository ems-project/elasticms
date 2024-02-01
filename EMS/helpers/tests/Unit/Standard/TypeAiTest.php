<?php

declare(strict_types=1);

namespace EMS\Helpers\Tests\Unit\Standard;

use EMS\Helpers\Standard\Type;
use PHPUnit\Framework\TestCase;

class TypeAiTest extends TestCase
{
    public function testStringWithValidString()
    {
        $this->assertEquals('test', Type::string('test'));
    }

    public function testStringWithInvalidType()
    {
        $this->expectException(\RuntimeException::class);
        Type::string(123);
    }

    public function testIntegerWithValidInteger()
    {
        $this->assertEquals(123, Type::integer(123));
    }

    public function testIntegerWithInvalidType()
    {
        $this->expectException(\RuntimeException::class);
        Type::integer('test');
    }

    public function testArrayWithValidArray()
    {
        $this->assertEquals(['key' => 'value'], Type::array(['key' => 'value']));
    }

    public function testArrayWithInvalidType()
    {
        $this->expectException(\RuntimeException::class);
        Type::array('not an array');
    }

    public function testGdImageWithValidGdImage()
    {
        $image = \imagecreatetruecolor(100, 100);
        $this->assertInstanceOf(\GdImage::class, Type::gdImage($image));
        \imagedestroy($image);
    }

    public function testGdImageWithInvalidType()
    {
        $this->expectException(\RuntimeException::class);
        Type::gdImage('not a gd image');
    }
}
