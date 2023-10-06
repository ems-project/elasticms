<?php

namespace EMS\Tests\CommonBundle\Common;

use EMS\CommonBundle\Common\Converter;
use PHPUnit\Framework\TestCase;

class ConverterAiTest extends TestCase
{
    public function testToAscii(): void
    {
        $this->assertEquals('hello-world', Converter::toAscii('Hello World'));
        $this->assertEquals('emsasset', Converter::toAscii('ems://asset'));
        $this->assertEquals('a-e-i-o-u', Converter::toAscii('À É Í Ó Ú'));
    }

    public function testFormatBytes(): void
    {
        $this->assertEquals('1 B', Converter::formatBytes(1));
        $this->assertEquals('1 KB', Converter::formatBytes(1024));
        $this->assertEquals('1 MB', Converter::formatBytes(1024 * 1024));
    }

    public function testStringify(): void
    {
        $this->assertEquals('hello', Converter::stringify('hello'));
        $this->assertEquals('123', Converter::stringify(123));
        $this->assertEquals('{"key":"value"}', Converter::stringify(['key' => 'value']));
    }
}
